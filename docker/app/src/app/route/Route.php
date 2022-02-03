<?php

namespace App\Route;

require __DIR__ . '/../../vendor/autoload.php';

use App\Controller\ProductController;
use App\libs\Common;

use FastRoute\Dispatcher;

use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

class Route
{
    /*
     *  ルーティング定義部分
    **/

    // ルーティングのアドレスを作成する
    public static function route() {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            //ルーティング記述基本ルール
            // $r->addRoute('REQUEST_METHOD', 'アクセス先URL', 'Controller@Method');

            // *****TOPページを表示する*****
            $r->addRoute('GET', '/', 'ProductController@getNewProducts');

            // *****ユーザー登録機能*****
            // 仮登録ページを表示する
            // GET
            $r->addRoute('GET', '/signup', 'CustomerController@signupForm');
            // 仮登録確認ページを表示する
            $r->addRoute('POST', '/signup', 'CustomerController@signupConfirm');
            // POST
            // $r->addRoute('POST', '/signupResult', 'CustomerController@signupResult');

            // work_会員登録再確認処理
            $r->addRoute('GET', '/entry_confirm/', 'CustomerController@viewEntryUserConfirm');
            $r->addRoute('POST', '/entry_confirm', 'CustomerController@entryUserConfirm');
            // 会員登録処理
            $r->addRoute('GET', '/entry/', 'CustomerController@entryUserForm');
            // $r->addRoute('GET', '/entry', 'CustomerController@entryUserForm');
            $r->addRoute('POST', '/entry', 'CustomerController@entryUser');
            // *****ログイン機能*****
            $r->addRoute('GET', '/login', 'CustomerController@getLogin');
            $r->addRoute('POST', '/login', 'CustomerController@postLogin');
            // ログアウト
            $r->addRoute('GET', '/logout', 'CustomerController@logout');
            // パスワード再設定
            $r->addRoute('GET', '/password', 'CustomerController@viewPasswordReconfigure');
            $r->addRoute('POST', '/password', 'CustomerController@passwordReconfigure');
            // *****パスワードリセット*****
            // パスワードリセット画面表示
            $r->addRoute('GET', '/passreset', 'CustomerController@viewPasswordReset');
            // パスワードトークン生成、メール送信
            $r->addRoute('POST', '/passreset', 'CustomerController@passwordResetResult');
            // 新しいパスワード入力画面
            // $r->addRoute('GET', '/newpass', 'CustomerController@viewNewPassword');
            $r->addRoute('GET', '/newpass/', 'CustomerController@viewNewPassword');
            // パスワードリセット処理
            $r->addRoute('POST', '/newpass', 'CustomerController@newPasswordResult');

            // *****マイページ*****
            $r->addRoute('GET', '/mypage', 'CustomerController@mypage');
            // *****会員情報詳細*****
            $r->addRoute('GET', '/userinfo', 'CustomerController@userinfo');
            $r->addRoute('POST', '/userinfoedC', 'CustomerController@userinfoEditedC');
            $r->addRoute('POST', '/userinfoedD', 'CustomerController@userinfoEditedD');


            // *****退会*****
            $r->addRoute('GET', '/withdraw', 'CustomerController@viewWithdrawed');
            $r->addRoute('GET', '/withdrawed', 'CustomerController@withdrawed');

            // *****商品表示機能*****
            // 商品の一覧を表示する
            $r->addRoute('GET', '/products', 'ProductController@showProducts');
            // 在庫あり商品の一覧を表示する
            $r->addRoute('POST', '/products', 'ProductController@setSearchWordAndCat');
            // 商品の個別ページを表示する
            $r->addRoute('GET', '/product/{id:\d+}', 'ProductController@showProduct');

            // *****商品検索機能*****
            // 商品の検索結果を表示する
            $r->addRoute('GET', '/search', 'ProductController@searchProducts');

            // *****カート機能*****
            // カートを表示する
            $r->addRoute('GET', '/cart', 'CartController@showCart');

            // 商品をカートに追加する
            $r->addRoute('POST', '/cart', 'CartController@addCart');

            // カートの数量を変更する

            // カートの商品を削除する
            $r->addRoute('POST', '/cart/del/{id:\d+}', 'CartController@delCartItem');

            // *****商品注文機能*****
            // 注文フォームを表示する
            $r->addRoute('GET', '/order', 'OrderController@viewOrderForm');
            $r->addRoute('POST', '/order', 'OrderController@viewOrderForm');

            // 注文確認画面を表示する
            $r->addRoute('POST', '/order/confirm', 'OrderController@confirmOrder');

            // 注文を確定する
            $r->addRoute('POST', '/order/complete', 'OrderController@completeOrder');

            // 注文履歴(一覧)を表示する
            $r->addRoute('GET', '/order/history', 'OrderController@showOrder');

            // 注文履歴(詳細)を表示する
            $r->addRoute('GET', '/order/detail/{id:\d+}', 'OrderController@showOrderDetail');

            // *****FAQ表示機能*****
            $r->addRoute('GET', '/faq', 'ProductController@faq');

            // Session閲覧用（release時に消す）
            // $r->addRoute('GET', '/showsession','ProductController@showSession');
        });

        // リクエストパラメーターを取得
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        // var_dump($httpMethod);
        $uri = $_SERVER['REQUEST_URI'];

        // var_dump($uri);
        // リクエストURLからクエリストリングを除去してURIをデコード
        // パラメータが必要な場合Controllerで$_GETを使用する
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        // ルーティング情報を取得
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        // ルーティングを実行する
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                // ルーティング先が見つかった場合の処理
                $handler = $routeInfo[1];
                // var_dump($handler);
                if ($handler !== '') {
                    $vars = $routeInfo[2];
                    list($class, $method) = explode('@', $handler, 2);
                    $full_class = 'App\\Controller\\' . $class;
                    call_user_func_array(array(new $full_class, $method), [$vars]);
                } else {
                    @session_start();
                    $page = new \Smarty();
                    try {
                        $page->display('index.tpl');
                    } catch (\SmartyException $e) {
                        error_log($e);
                    }
                }
                break;

            case Dispatcher::NOT_FOUND:
                // ルーティング先が見つからなかった場合の処理
                // TODO :404ページ作りますか？
                // TODO :暫定でTOPにリダイレクトさせます
                Common::sendRedirect('/');
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                // 許可されていないメソッドでアクセスしようとした時の処理
                // TODO:405ページ作りますか？
                $allowedMethods = $routeInfo[1];
                Common::sendRedirect('/');
                // echo 'Method Not Allowed. allow only=' . json_encode($allowedMethods);
                break;

            default:
                // TODO:500ページ作りますか？
                // TODO:エラー遷移先のページを作るべきですか？
                // TODO: 「エラー！」って出るところつくるそうです
                echo '500 Server Error';
                break;
        }
    }

}