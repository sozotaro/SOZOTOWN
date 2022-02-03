<?php

namespace App\Controller;

use App\libs\Common;
use App\libs\Pagenation;
use App\Model\Order;
use App\Model\Product;
use App\Model\Customer;

class ProductController
{
    private $view;
    private $model;

    public function __construct() {
        $this->view = new \Smarty();
        $this->pagenation = new Pagenation();
        $this->model = new Product();
        $this->customer = new Customer();
    }

    // 商品一覧を表示する
    public function showProducts() {
        @session_start();
        $err_msgs = Common::getErrorMsgs();
        Order::removeOrderSession();

        // $this->model->removeStockInfoSession();
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
        //検索
        $searchword = NULL;
        if(isset ($_SESSION['word'])){
            $searchword = htmlspecialchars($_SESSION['word']);
            unset($_SESSION['word']);
        }
        //category
        $catid = 0;
        if(isset ($_SESSION['category'])){
            //数字かどうか
            if(ctype_digit($_SESSION['category'])){
                die("不正アクセス");
            }else{
                $catid = (int)htmlspecialchars($_SESSION['category']);
                unset($_SESSION['category']);
            }
        }
        // var_dump($searchword);
        // 商品リスト出力
        $results = $this->model->GetByStocks($p, $searchword, $catid);
        $number = 1;
        if($results == false){
            $no_choice = "表示される商品はありません。";
        }else {
            $no_choice = false;
            // 商品ID数確認
            $number = $this->model->GetQuantityByStocks($searchword, $catid);
            // var_dump($number);
        }
        // pagenation
        $page = $this->pagenation->pagenation_list($p, $number);
        // var_dump($page);
        $category_name = false;
        if($catid != 0){
            $category_name = $this->model->GetNameByCategory($catid);
            // var_dump($category_name);
        }
        if($searchword == NULL){
            $searchword = false;
        }
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
        $this->view->assign('search_word',$searchword);
        $this->view->assign('category_name',$category_name);
        $this->view->assign('no_choice',$no_choice);
        $this->view->assign('results',$results);
        $this->view->assign('err_msgs',$err_msgs);
        try {
            $this->view->display('show_products.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }
    // POST処理
    public function setSearchWordAndCat(){
        @session_start();
        //インジェクション処理
        if(isset($_POST['word'])){
            $word = Common::trimSpace(htmlspecialchars($_POST['word']));
            $_SESSION["word"] = $word;
        }
        if(isset($_POST['category'])){
            $category = Common::trimSpace(htmlspecialchars($_POST['category']));
            $_SESSION["category"] = (int)$category;
            // var_dump($_SESSION["category"]);
        }
        if(isset($_POST['clear'])){
            if(isset($_SESSION['category']) ||isset($_SESSION['word'])){
                unset($_SESSION['category']);
                unset($_SESSION['word']);
            }
        }
        Common::sendRedirect($_SERVER['REQUEST_URI']);
    }

    // FAQ表示
    public function faq(){
        try {
            $this->view->display('faq.tpl');
        } catch (\SmartyException $e) {
            // var_dump($e);
            error_log($e);
        }
    }

    // 個別の商品情報を表示する
    public function showProduct($id) {
        @session_start();
        $results = $this->model->getProduct($id);
        if (!$results){
            header('location:/products');
            exit();
        }
        // 最低価格を取得する
        $minimum = min(array_column($results, 'price'));
        $key = Common::getCsrfKey();
        $_SESSION['key'] = $key;
        $this->view->assign(compact('results','minimum','key'));
        try {
            $this->view->display('show_product.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    // 商品名で検索する
    public function searchProducts() {
        $item = htmlspecialchars($_GET['word']);
        $results = $this->model->searchProducts($item);
        $err_msgs = Common::getErrorMsgs();
        // var_dump($err_msgs);
        $this->view->assign(compact('results','err_msgs'));
        try {
            $this->view->display('show_products.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    // 新商品を取得する
    public function getNewProducts() {
        $results = $this->model->getNewProducts();
        $this->view->assign('results', $results);
        try {
            $this->view->display('index.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    // Session確認用
    public function showSession(){
        @session_start();
        // var_dump($_SESSION);
    }

}