<?php

namespace App\libs;

class Common
{
    /**
     * CSRFトークンを生成する
     */
    public static function getCsrfKey(): string {
        $key = md5(uniqid() . mt_rand());
        $_SESSION['key'] = $key;
        return $key;
    }

    /**
     * CSRFトークンをチェックする
     */
    public static function checkCsrfKey() {
        //var_dump('sesion'.$_SESSION['key']);
        //var_dump('post'.$_POST['key']);
        //exit();
        if (isset($_SESSION["key"], $_POST["key"]) && $_SESSION["key"] == $_POST["key"]) {
            unset($_SESSION["key"]);
            return true;
        } else {
            $err_msg = 'CSRFエラー';
            $_SESSION['err_msgs'][] = $err_msg;
            Common::sendRedirect($_SERVER['HTTP_REFERER']);
        }
    }

    // リダイレクト処理
    public static function sendRedirect(string $url) {
        header('location:' . $url);
        exit();
    }

    // 特定エラーをハンドリングした時の処理
    public static function sendErrorRedirect(string $err_msg) {
        if (!empty($err_msg)) {
            echo "<script>alert('$err_msg');
              window.location.href='/';
              </script>";
            exit();
        }
    }

    //エラー処理を取得
    public static function getErrorMsgs() {
        @session_start();
        if (!isset($err_msgs)) {
            $err_msgs = array();
        }
        if (isset($_SESSION['err_msgs'])) {
            $err_msgs = $_SESSION['err_msgs'];
            unset($_SESSION['err_msgs']);
        }
        return $err_msgs;
    }

    // セッション破棄(ログアウト)
    public static function removeAuthSession() {
        @session_start();
        // if(isset($_SESSION['user_info'])){
        //     unset($_SESSION['user_info']);
        // }
        // if(isset($_SESSION['customer_id'])){
        //     unset($_SESSION['customer_id']);
        // }
        // if(isset($_SESSION['nickname'])){
        //     unset($_SESSION['nickname']);
        // }
        session_destroy();
    }

    // セッションチェック
    public static function checkLoginSession() {
        @session_start();
        if (!isset($_SESSION['customer_id']) || !isset($_SESSION['nickname'])) {
            header('location:/login');
            exit();
        }
    }

    // セッション破棄(商品検索)
    public static function removeStockInfoSession() {
        @session_start();
        if (isset($_SESSION["word"])) {
            unset($_SESSION["word"]);
        }
        if (isset($_SESSION["category"])) {
            unset($_SESSION["category"]);
        }
        if (isset($_SESSION["max"])) {
            unset($_SESSION["max"]);
        }
    }

    // フォーム内の空白とカンマを削除する
    public static function trimSpace($source) {
        $table = array(
            ' ' => '',
            '　' => '',
            ',' => ''
        );
        $search = array_keys($table);
        $replace = array_values($table);
        return str_replace($search, $replace, $source);
    }

}