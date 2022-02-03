<?php

namespace App\Controller;

use App\Model\Cart;
use App\Model\Customer;
use App\Model\Order;
use App\Model\Mail;
use Dotenv\Dotenv;
use App\libs\Pagenation;
use App\libs\Common;
use App\libs\Validator;

require(__DIR__ . '/../../vendor/autoload.php');
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class OrderController
{
    private $view;
    private $model;

    public function __construct() {
        $this->view = new \Smarty();
        $this->pagenation = new Pagenation();
        $this->model = new Order();
        $this->mail = new Mail();
        $this->cart = new Cart();
    }

    /**
     * 配送先入力ページを表示する
     */
    public function viewOrderForm() {
        @session_start();
        Common::checkLoginSession();
        $err_msgs = Common::getErrorMsgs();
        $cart = $this->cart->getCartData();
        if(!empty($err_msgs['key'])){
            $_POST['key'] = $err_msgs['key'];
        }
        if(empty($cart) || empty($_POST['key'])){
            header('location:/cart');
            exit();
        }

        Common::checkCsrfKey();
        $key = Common::getCsrfKey();
        $prefs = Order::getPref();

        // 住所を取得
        if (isset($_SESSION['customer_id'])){
            $customer_id = $_SESSION['customer_id'];
            $delivery = (new Customer)->getDelivery($customer_id);

            $_SESSION['name'] = $delivery[1]['name'];
            $_SESSION['name_kana'] = $delivery[1]['name_kana'];
            $_SESSION['post_number'] = $delivery[1]['post_number'];
            $_SESSION['telephone_number'] = $delivery[1]['telephone_number'];
            $_SESSION['pref'] = $delivery[1]['area_id'];
            $_SESSION['city'] = $delivery[2][0];
            $_SESSION['street'] = $delivery[2][1];
            $_SESSION['building'] = $delivery[2][2];
        }

        $_SESSION['email_address'] = (new \App\Model\Customer)->getEmail($_SESSION['customer_id'])['email_address'];

        $this->view->assign(compact('prefs', 'key', 'err_msgs'));
        try {
            $this->view->display('order_form.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    /**
     * 購入情報確認ページを表示する
     */
    public function confirmOrder() {
        @session_start();
        // CSRF
        // Common::checkCsrfKey();
        $key = Common::getCsrfKey();

        // フォームの内容をセッションに登録する
        $_SESSION['name'] = Common::trimSpace(htmlspecialchars($_POST['name']));
        $_SESSION['name_kana'] = Common::trimSpace(htmlspecialchars($_POST['name_kana']));
        $_SESSION['post_number'] = Common::trimSpace(htmlspecialchars($_POST['post_number']));
        $_SESSION['telephone_number'] = Common::trimSpace(htmlspecialchars($_POST['telephone_number']));
        $_SESSION['pref'] = (int)Common::trimSpace(htmlspecialchars($_POST['pref']));
        $_SESSION['city'] = Common::trimSpace(htmlspecialchars($_POST['city']));
        $_SESSION['street'] = Common::trimSpace(htmlspecialchars($_POST['street']));
        $_SESSION['building'] = Common::trimSpace(htmlspecialchars($_POST['building']));
        $_SESSION['address'] = implode(',',array($_SESSION['city'],$_SESSION['street'],$_SESSION['building']));

        // $_POSTにvalidationチェック
        $err_msgs = Validator::validate(
            array(
                'name' => $_SESSION['name'],
                'name_kana' => $_SESSION['name_kana'],
                'post_number' => $_SESSION['post_number'],
                'telephone_number' => $_SESSION['telephone_number'],
                'pref' => $_SESSION['pref'],
                'city' => $_SESSION['city'],
                'street' => $_SESSION['street'],
                'building' => $_SESSION['building']
            )
        );

        if (!empty($err_msgs['name']) ||
            !empty($err_msgs['name_kana']) ||
            !empty($err_msgs['city']) ||
            !empty($err_msgs['street']) ||
            !empty($err_msgs['building']) ||
            !empty($err_msgs['pref']) ||
            !empty($err_msgs['telephone_number']) ||
            !empty($err_msgs['post_number'])) {
            @session_start();
            $key = Common::getCsrfKey();
            $err_msgs['key'] = $key;
            $_SESSION['err_msgs'] = $err_msgs;
            header('location:/order');
            exit();
        }

        // カートの情報を取得する
        $cart = new Cart();
        $carts = $cart->getCartData();
        $_SESSION['carts'] = $carts;
        empty($results) ?: die('カートに商品がありません');

        $subtotal = array_sum(array_column($carts, 'price'));
        if ($subtotal > 0) {
            $_SESSION['subtotal'] = $subtotal;
            $_SESSION['tax'] = $subtotal * ((int) $_ENV['CONSUMPTION_TAX'] / 100);
            $_SESSION['charge'] = (int) $_ENV['CHARGE'];
            $_SESSION['amount'] = $_SESSION['subtotal'] + $_SESSION['tax'] + $_SESSION['charge'];
        }

        $order = new Order();
        $pref = $order->getPrefById($_SESSION['pref']);
        $this->view->assign(compact('carts', 'pref', 'subtotal', 'key'));

        try {
            $this->view->display('order_confirm.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    /**
     * 注文を確定する
     */
    public function completeOrder() {
        @session_start();
        $carts = $_SESSION['carts'];
        $this->model->orderRegistration($carts);
        $to = $_SESSION['email_address'];
        // 注文確定メールを送信する
        $this->mail->OrderSendMail($to, $carts);
        // セッションクリア
        Order::removeOrderSession();
        try {
            $this->view->display('order_complete.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
        
    }

    /**
     * 注文履歴一覧を表示する
     */
    public function showOrder(){
        @session_start();
        Common::checkLoginSession();
        if(isset($_SESSION['customer_id'])){
            $customer_id = $_SESSION['customer_id'];
        }else{
            die('ユーザーデータを取得できませんでした');
        }
        $p = 1;
        if(isset ($_GET['p'])){
            // 有効な数字かどうか
            if(($_GET['p']) > ($_SESSION['max'])){
                $p = 1;
                Common::removeStockInfoSession();
            }else {
                $pageRequest = (int)htmlspecialchars($_GET['p']);
                $p = $pageRequest;
            }
        }
        if (isset($_GET['s'])){
            $s = '&s='.(int)htmlspecialchars( $_GET['s'] );
            $desc = '';
        }else{
            $desc = 'DESC';
            $s = '';
        }
        $orders = $this->model->getOrderList($p,$customer_id,$desc);
        // var_dump($orders);
        // 購入履歴数確認
        $number = $this->model->GetQuantityByOrderHistories($customer_id);
        // var_dump($number);
        // pagenation
        $page = $this->pagenation->order_pagenation_list($p, $number);
        // var_dump($page);
        if(isset ($page)){
            $page_first = $page['first'];
            $page_next = $page['next'];
            $page_status = $page['status'];
            $page_last = $page['last'];
            $page_final = $page['final'];
            $this->view->assign('page_first',$page_first);
            $this->view->assign('page_next',$page_next);
            $this->view->assign('page_status',$page_status);
            $this->view->assign('page_last',$page_last);
            $this->view->assign('page_final',$page_final);
        }
        $this->view->assign(compact('orders','s'));
        try {
            $this->view->display('order_history.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    /**
     * 注文履歴の詳細を表示する
     */
    public function showOrderDetail($order_id){
        @session_start();
        Common::checkLoginSession();
        $order_id = (int)$order_id['id'];
        $orders = $this->model->getOrderDetail($order_id);
        $this->view->assign('orders', $orders);
        try {
            $this->view->display('order_detail.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

}