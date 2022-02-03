<?php

namespace App\Controller;

use App\Model\Cart;
use App\libs\Common;

class CartController
{
    private $view;
    private $model;

    public function __construct() {
        $this->view = new \Smarty();
        $this->model = new Cart();
    }

    /**
     * カートに商品を追加する
     */
    public function addCart() {
        @session_start();
        // ログインチェック
        // CSRF
        Common::checkCsrfKey();
        $result = $this->model->addCart();

        if (!$result) {
            echo '<h1>カートの追加に失敗しました</h1>';
            return;
        }
        Common::sendRedirect('/cart');
    }

    /**
     * カートの商品を表示する
     */
    public function showCart() {
        @session_start();
        // ログインチェック
        $results = $this->model->getCartData();
        $subtotal = array_sum(array_column($results, 'price'));
        $key = Common::getCsrfKey();
        $err_msgs = Common::getErrorMsgs();
        $this->view->assign(compact('results', 'subtotal', 'key', 'err_msgs'));
        try {
            $this->view->display('my_cart.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    /**
     * カートのアイテムを削除する
     * @param $cart_id
     */
    public function delCartItem($cart_id) {
        @session_start();
        $result = $this->model->delCartItem($cart_id['id']);
        if ($result) {
            header('location:/cart');
        } else {
            die('カートを削除できませんでした');
        }
    }

}