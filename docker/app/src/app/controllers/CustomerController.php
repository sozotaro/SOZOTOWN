<?php

namespace App\Controller;

use App\Model\Customer;
use App\Model\Mail;
use App\Model\Order;
use App\libs\Common;
use App\libs\Pagenation;
use App\libs\Validator;


class CustomerController
{
    private $view;
    private $model;
    private $mail;
    private $order;

    public function __construct()
    {
        $this->view = new \Smarty();
        $this->pagenation = new Pagenation();
        $this->model = new Customer();
        $this->mail = new Mail();
        $this->order = new Order();
    }

    // 会員登録フォーム(パスワード入力)
    public function entryUserForm(){
        @session_start();
        if(!isset($_GET['id']) || !isset($_GET['shualp'])){
            header('location:/');
            exit();
        }
    
        $err_msgs = Common::getErrorMsgs();
    
        $id = htmlspecialchars($_GET['id']);
        $shualp = htmlspecialchars($_GET['shualp']);
        $_SESSION['id'] = $id;
        $_SESSION['shualp'] = $shualp;

        $key = Common::getCsrfKey();
        $_SESSION['key'] = $key;
        try {
            $this->view->assign(compact('err_msgs', 'key'));
            // $this->view->display('entry_form.tpl');
            $this->view->display('entry_confirm.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    // 会員登録処理
    public function entryUser(){
        @session_start();
        if(!isset($_SESSION['id'])){
            header('location:/');
            exit();
        }
        Common::checkCsrfKey();
        $id = $_SESSION['id'];
        $shualp = $_SESSION['shualp'];

        $password = Common::trimSpace(htmlspecialchars($_POST['password']));
        $err_msgs = Validator::validate(
            array(
                'password'=> $password
            )
        );

        if (!empty($err_msgs['password'])) {
            @session_start();
            $_SESSION['err_msgs'] = $err_msgs;
            // var_dump($_SESSION['err_msgs']);
            $url = "/entry/?id=".$id."&shualp=".$shualp;
            header('location:'.$url);
            exit();
        }

        // $err_msgs = array();
        // if (empty($password)) {
        //     array_push($err_msgs, 'パスワードが空欄です。');
        //     $_SESSION['err_msgs'] = $err_msgs;
        //     $url = "/entry/?id=".$id."&shualp=".$shualp;
        //     header('location:'.$url);
        //     exit();
        // }
       
        $results = $this->model->tempoRegisSelect($id);
        // var_dump($results);
        // exit();
        // var_dump($results);
        if(!$results[0] || !$results[1]){
            $this->model->dbNull();
            Common::removeAuthSession();
            $err_msgs = '無効なアドレスです。';
            Common::sendErrorRedirect($err_msgs);
            // die("無効なアドレスです。");
        }
        $customer_id = $results[0]['customer_id']; //仮登録テーブルより取得 customer_id
        // $top_secret = $results['password_token']; //仮登録テーブルより取得  暗号文
        $karidate = $results[0]['temporary_registration_at']; //仮登録テーブルより取得 仮登録日時
        $password_token = $results[1]['password'];
        // var_dump($customer_id);
        // var_dump($karidate);
        // var_dump($password_token);
        // exit();
        
        if ($karidate !== NULL) {
            if(!Customer::isValidUser($customer_id)){
                Common::removeAuthSession();
                $err_msgs = 'アカウントはロックされています。';
                Common::sendErrorRedirect($err_msgs);
                // array_push($err_msgs, 'アカウントはロックされています。');
                // var_dump($err_msgs);
                // @session_start();
                // $_SESSION['err_msgs'] = $err_msgs;
                // $url = "/entry/?id=".$id."&shualp=".$shualp;
                // header('location:'.$url);
                exit();
            }
            if(!Customer::lockedUser($customer_id)){
                Common::removeAuthSession();
                $err_msgs = 'アカウントはロックされています。';
                Common::sendErrorRedirect($err_msgs);
                // array_push($err_msgs, 'アカウントはロックされています。');
                // @session_start();
                // $_SESSION['err_msgs'] = $err_msgs;
                // $url = "/entry/?id=".$id."&shualp=".$shualp;
                // header('location:'.$url);
                exit();
            }
            if (password_verify($password, $password_token)) {
                //顧客テーブルに登録日時をUPDATE & 仮登録 & ニックネーム取得
                $nickname = $this->model->entryDateAndSelectNickname($customer_id);
                Customer::lockedNULL($customer_id);
                Customer::clearFails($customer_id);
                Common::removeAuthSession();
                //view
                try {
                    $this->view->assign('nickname', $nickname);
                    $this->view->display('entry_result.tpl');
                } catch (\SmartyException $e) {
                    error_log($e);
                }
            } else {
                Customer::createLoginFails($customer_id);
                Common::removeAuthSession();
                $err_msgs['password'][0]= 'パスワードが違います。';
                // array_push($err_msgs, '何かが違うんだわ。もう一回。');
                @session_start();
                $_SESSION['err_msgs'] = $err_msgs;
                $url = "/entry/?id=".$id."&shualp=".$shualp;
                header('location:'.$url);
                exit();
            }
        } else {
            $this->model->dbNull();
            Common::removeAuthSession();
            $err_msgs = '失効済みです。';
            Common::sendErrorRedirect($err_msgs);
            exit();
            // die("失効済みです。");
        }
    }

    // work_会員登録確認表示処理_直打ちURLの際、GET処理におけるエラーが画面表示されてしまう件
    public function viewEntryUserConfirm(){

        // work_セッション情報処理
        @session_start();

        // work_対象外画面遷移処理
        $id = htmlspecialchars($_GET['id']);
        $shualp = htmlspecialchars($_GET['shualp']);
        $_SESSION['id'] = $id;
        $_SESSION['shualp'] = $shualp;
        if (empty($id) || empty($shualp)) {
            header('location:/');
            exit();
        }

        // work_エラーメッセージ処理_sessionにて取得してくるパターンてなに？
        $err_msgs = Common::getErrorMsgs();

        // work_CSRF処理_nanimono
        $key = Common::getCsrfKey();
        $_SESSION['key'] = $key;

        // work_表示処理
        try {
            $this->view->assign(compact('err_msgs', 'key'));
            $this->view->display('entry_confirm.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    // work_会員登録確認動作処理
    public function entryUserConfirm(){

        // work_セッション情報処理
        @session_start();
        
        // work_CSRF処理_nanimono_korenoimi
        Common::checkCsrfKey();

        // work_各種情報取得
        // $id = htmlspecialchars($_GET['id']);
        // $shualp = htmlspecialchars($_GET['shualp']);
        $password = Common::trimSpace(htmlspecialchars($_POST['password']));
        $id = $_SESSION['id'];
        $shualp = $_SESSION['shualp'];

        // work_仮登録情報取得
        $results = $this->model->tempoRegisSelect($id);
        $customer_id = $results[0]['customer_id']; //仮登録テーブルより取得 customer_id
        // $top_secret = $results['password_token']; //仮登録テーブルより取得  暗号文
        $karidate = $results[0]['temporary_registration_at']; //仮登録テーブルより取得 仮登録日時
        $password_token = $results[1]['password'];
        // var_dump($customer_id);
        // var_dump($karidate);
        // var_dump($password_token);
        // exit();

        // work_バリデーション処理
        $err_msgs = array();
        if (empty($password)) {
            array_push($err_msgs, 'パスワードが空欄です');
        }
        if (count($err_msgs)) {
            @session_start();
            $_SESSION['err_msgs'] = $err_msgs;
            Common::sendRedirect($_SERVER['REQUEST_URI']);
            exit();
        }

        // work_仮ユーザー情報認証
        if ($karidate !== NULL) {

            // work_仮ユーザーパスワード認証
            if (password_verify($password, $password_token)) {
                $nickname = $this->model->entryDateAndSelectNickname($customer_id);
                try {
                    $this->view->assign('nickname', $nickname);
                    $this->view->display('entry_result.tpl');
                } catch (\SmartyException $e) {
                    error_log($e);
                }
            } else {
                // work_ブルートフォース対策仕込むべき？
                array_push($err_msgs, 'パスワードが違います');
                if (count($err_msgs)) {
                    @session_start();
                    $_SESSION['err_msgs'] = $err_msgs;
                    Common::sendRedirect($_SERVER['REQUEST_URI']);
                    exit();
                }
            }
        } else {
            // work_即killでおk？
            die("仮登録情報が存在しません");
        }
    }

    // 仮会員登録フォーム
    public function signupForm(){
        @session_start();
        if(isset($_SESSION['customer_id'])&&isset($_SESSION['nickname'])){
            header('location:/');
        }
        //最初にログアウト
        // Common::removeAuthSession();
        //エラーを取得
        $err_msgs = Common::getErrorMsgs();
        $key = Common::getCsrfKey();
        $_SESSION['key'] = $key;
        $this->view->assign(compact('err_msgs', 'key'));
        try {
            $this->view->display('tempo_regis_form.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

    // 仮会員確認画面
    public function signupConfirm(){
        @session_start();
        Common::checkCsrfKey(); 

        // $key = Common::getCsrfKey();
        // $_SESSION['key'] = $key;

        // var_dump($err_msgs);
        // var_dump($err_msgs['email_address'][0]);

//        $err_msgs = $this->validator($_POST);
//        var_dump($err_msgs);

        //USERIDとPASSWORDを取得
        $nickname = Common::trimSpace(htmlspecialchars($_POST['nickname']));
        $email_address = Common::trimSpace(htmlspecialchars($_POST['email_address']));
        $email_address_confirm = Common::trimSpace(htmlspecialchars($_POST['email_address_confirm']));
        $password = Common::trimSpace(htmlspecialchars($_POST['password']));
        $password_confirm = Common::trimSpace(htmlspecialchars($_POST['password_confirm']));

        $err_msgs = Validator::validate(
            array(
                'nickname' => $nickname,
                'email_address' => $email_address,
                'email_address_confirm' => $email_address_confirm,
                'password' => $password,
                'password_confirm' => $password_confirm
            )
        );

        if (!empty($err_msgs['email_address']) ||
            !empty($err_msgs['email_address_confirm']) ||
            !empty($err_msgs['password']) ||
            !empty($err_msgs['password_confirm']) ||
            !empty($err_msgs['nickname'])) {
            @session_start();
            $_SESSION['err_msgs'] = $err_msgs;
            // var_dump($_SESSION['err_msgs']['email_address'][0]);
            Common::sendRedirect($_SERVER['REQUEST_URI']);
            exit();
        }
        // //認証処理
        // $user_info = array(
        //     'nickname' => $nickname,
        //     'email_address' => $email_address,
        //     'password' => password_hash($password, PASSWORD_DEFAULT),
        // );
        // $_SESSION['user_info'] = $user_info;

        $password = password_hash($password, PASSWORD_DEFAULT);

        // ユーザーID作成処理
        $alp = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz0123456789';
        //仮のユーザID
        $id = substr(str_shuffle($alp), 0, 8);
        // 暗号文作成処理
        $shualp = substr(str_shuffle($alp), 0, 8);
      
        $top_secret = password_hash($shualp, PASSWORD_DEFAULT);
        // URL作成_work_entry=>entry_confirmに要変更_サーバーにおけるポート番号変更の際に要対応箇所

        // 別環境で動かす場合はENVのSERVER_URIを書き換える
        $url = SERVER_URI . "entry/?id=" . $id . "&shualp=" . $shualp;
        
        $message = "下記のURLをクリックして24時間以内に本登録を完了してください。\r\n" . $url;

        //メール重複確認
        $this->model->countEmail($email_address);
        // 顧客テーブルにINSERT & 仮登録テーブルにINSERT
        $this->model->customerInsert($email_address, $password, $nickname, $id, $top_secret);
        // メール送信
        $this->mail->tempoRegisSendMail($email_address, $nickname, $message);
        // セッション破棄
        $this->model->removeUserinfoSession();
        try {
            $this->view->display('signup_send_mail.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
        
        // var_dump($user_info);
        // $this->view->assign(compact('user_info','key'));
        // try {
        //     $this->view->display('tempo_regis_result.tpl');
        // } catch (\SmartyException $e) {
        //     error_log($e);
        // }
        
        // exit();
    }

    // 仮会員登録処理
    // public function signupResult(){
    //     @session_start();
    //     Common::checkCsrfKey(); 
    //     $user_info = $_SESSION['user_info'];
    //     // var_dump($user_info);
    //     $to = $user_info["email_address"];
    //     // var_dump($to);
    //     // exit();
    //     $password = $user_info["password"];
    //     $nickname = $user_info["nickname"];

    //     // ユーザーID作成処理
    //     $alp = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz0123456789';
    //     //仮のユーザID
    //     $id = substr(str_shuffle($alp), 0, 8);
    //     // 暗号文作成処理
    //     $shualp = substr(str_shuffle($alp), 0, 8);
    //     $shualp2 = substr(str_shuffle($alp), 0, 8);
    //     $top_secret = password_hash($shualp2, PASSWORD_DEFAULT);
    //     // URL作成
    //     $url = "http://localhost:18080/entry/?id=" . $id . "&shualp=" . $shualp;
    //     $message = "下記のURLをクリックして24時間以内に本登録を完了してください。\r\n" . $url;

    //     //メール重複確認
    //     $this->model->countEmail($to);
    //     // 顧客テーブルにINSERT & 仮登録テーブルにINSERT
    //     $this->model->customerInsert($to, $password, $nickname, $id, $top_secret);
    //     // メール送信
    //     $this->mail->tempoRegisSendMail($to, $nickname, $message);
    //     // セッション破棄
    //     $this->model->removeUserinfoSession();
    //     try {
    //         $this->view->display('signup_send_mail.tpl');
    //     } catch (\SmartyException $e) {
    //         error_log($e);
    //     }
    // }
    
    // 退会確認画面
    public function viewWithdrawed(){
        @session_start();
        Common::checkLoginSession();
        try {
            $this->view->display('withdraw.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }
    // 退会処理
    public function withdrawed(){
        @session_start();
        Common::checkLoginSession();
        $customer_id = $_SESSION['customer_id'];
        $nickname = $_SESSION['nickname'];
        $to = $this->model->withdrawedUpdate($customer_id);
        // var_dump($to);
        if($to ===  false){
            $err_msgs = '退会失敗しました。';
            Common::sendErrorRedirect($err_msgs);
        }
        $this->mail->withdrawedSendMail($to, $nickname);
        Common::removeAuthSession();
        header('location:/');
        exit();
    }

    // 会員情報詳細画面
    public function userinfo(){
        @session_start();
        Common::checkLoginSession();
        $err_msgs = Common::getErrorMsgs();
        $customer_id = $_SESSION['customer_id'];
        $results = $this->model->getDelivery($customer_id);

        $key = Common::getCsrfKey();
        $_SESSION['key'] = $key;
        
        // var_dump($results);
        $prefs = Order::getPref();
        $nickname = $_SESSION['nickname'];
        $email_address = $results[0]['email_address'];
        $name = $results[1]['name'];
        $name_kana = $results[1]['name_kana'];
        $post_number = $results[1]['post_number'];
        $area_id = $results[1]['area_id'];
        $city = $results[2][0];
        $street = $results[2][1];
        $building = $results[2][2];
        $telephone_number = $results[1]['telephone_number'];
        try {
            $this->view->assign(compact('nickname','email_address','name','name_kana','post_number','prefs','area_id','city','street','building','telephone_number','err_msgs', 'key'));
            $this->view->display('userinfo.tpl');
        } catch (\SmartyException $e) {
        }
    }

    // 会員情報変更処理(customers)
    public function userinfoEditedC(){
        @session_start();
        Common::checkCsrfKey();
        Common::checkLoginSession();
        $customer_id = $_SESSION['customer_id'];
        $nickname = Common::trimSpace(htmlspecialchars($_POST['nickname']));
        $email_address = Common::trimSpace(htmlspecialchars($_POST['email_address']));

        $err_msgs = Validator::validate(
            array(
                'nickname' => $nickname,
                'email_address' => $email_address
            )
        );

        $err_msgs['name'] = NULL;
        $err_msgs['name_kana'] = NULL;
        $err_msgs['city'] = NULL;
        $err_msgs['street'] = NULL;
        $err_msgs['building'] = NULL;
        $err_msgs['pref'] = NULL;
        $err_msgs['post_number'] = NULL;
        $err_msgs['telephone_number'] = NULL;

        if (!empty($err_msgs['nickname']) || !empty($err_msgs['email_address'])) {
            @session_start();
            $_SESSION['err_msgs'] = $err_msgs;
            // var_dump($_SESSION['err_msgs']);
            header('location:/userinfo');
            exit();
        }

        $result = $this->model->updateCustomer($nickname, $email_address, $customer_id);
        if(!$result){
            $err_msgs['resultC'][0] =  '変更失敗しました。';
        }else{
            $err_msgs['resultC'][0] =  '変更しました。';
        }
        $_SESSION['err_msgs'] = $err_msgs;
        $_SESSION['nickname'] = $result;
        header('location:/userinfo');
        exit();
    }   
    // 会員情報変更処理(deliveries)
    public function userinfoEditedD(){
        @session_start();
        Common::checkCsrfKey();
        Common::checkLoginSession();
        $city = Common::trimSpace(htmlspecialchars($_POST['city']));
        $street = Common::trimSpace(htmlspecialchars($_POST['street']));
        $building = Common::trimSpace(htmlspecialchars($_POST['building']));
        $address = implode(',', array($city,$street,$building));

        $delivery = array();
        $delivery['customer_id'] = $_SESSION['customer_id'];
        $delivery['name'] = Common::trimSpace(htmlspecialchars($_POST['name']));
        $delivery['name_kana'] = Common::trimSpace(htmlspecialchars($_POST['name_kana']));
        $delivery['post_number'] = Common::trimSpace(htmlspecialchars($_POST['post_number']));
        $delivery['area_id'] = (int)Common::trimSpace(htmlspecialchars($_POST['pref']));
        $delivery['address'] = $address;
        $delivery['telephone_number'] = Common::trimSpace(htmlspecialchars($_POST['telephone_number']));

        $err_msgs = Validator::validate(
            array(
                'city' => $city,
                'street' => $street,
                'building' => $building,
                'name' => $delivery['name'],
                'name_kana' => $delivery['name_kana'],
                'post_number' => $delivery['post_number'],
                'pref' => $delivery['area_id'],
                'telephone_number' => $delivery['telephone_number']
            )
        );

        $err_msgs['nickname'] = NULL;
        $err_msgs['email_address'] = NULL; 
        if (!empty($err_msgs['name']) || 
            !empty($err_msgs['name_kana']) || 
            !empty($err_msgs['city']) ||
            !empty($err_msgs['street']) ||
            !empty($err_msgs['building']) ||
            !empty($err_msgs['pref']) ||
            !empty($err_msgs['post_number']) ||
            !empty($err_msgs['telephone_number'])) {
            @session_start();
            $_SESSION['err_msgs'] = $err_msgs;
            header('location:/userinfo');
            exit();
        }
        
        $result = $this->model->updateDelivery($delivery);
        if(!$result){
            $err_msgs['resultD'][0] =  '変更失敗しました。';
        }else{
            $err_msgs['resultD'][0] =  '変更しました。';
        }
        $_SESSION['err_msgs'] = $err_msgs;
        header('location:/userinfo');
        exit();
    }
    // パスワード再設定
    public function viewPasswordReconfigure(){
        @session_start();
        Common::checkLoginSession();

        $key = Common::getCsrfKey();
        $_SESSION['key'] = $key;

        $err_msgs = Common::getErrorMsgs();
        try {
            $this->view->assign(compact('err_msgs', 'key'));
            $this->view->display('password_reconfigure_form.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }
    // パスワード再設定処理
    public function passwordReconfigure(){
        @session_start();
        Common::checkCsrfKey();
        Common::checkLoginSession();
        $customer_id = $_SESSION['customer_id'];
        $password = Common::trimSpace(htmlspecialchars($_POST['password']));
        $new_password = Common::trimSpace(htmlspecialchars($_POST['new_password']));
        $new_password_confirm = Common::trimSpace(htmlspecialchars($_POST['new_password_confirm']));

        $err_msgs = Validator::validate(
            array(
                'password' => $password,
                'new_password' => $new_password,
                'new_password_confirm' => $new_password_confirm
            )
        );

        if(!empty($err_msgs['password'][0])){
            $err_msgs['password'][0] = str_replace('パスワード', '現在のパスワード', $err_msgs['password'][0]);
        }
        // if (empty($password)) {
        //     $err_msgs['password'][0] = '現在のパスワードが空欄です。';
        // }
        // if (empty($new_password)) {
        //     $err_msgs['new_password'][0] = '新しいパスワードが空欄です。';
        // }
        // if (empty($new_password_confirm)) {
        //     $err_msgs['new_password_confirm'][0] = '新しいパスワード(確認)が空欄です。';
        // }
        // if (!empty($new_password) && !empty($new_password_confirm) && $new_password !== $new_password_confirm) {
        //     $err_msgs['new_password'][0] = '新しいパスワードと新しいパスワード(確認)が一致しません。';
        // }
        if (!empty($err_msgs['password']) || !empty($err_msgs['new_password']) || !empty($err_msgs['new_password_confirm'])) {
            @session_start();
            $_SESSION['err_msgs'] = $err_msgs;
            Common::sendRedirect($_SERVER['REQUEST_URI']);
            exit();
        }

        $result = $this->model->setPasswordReconfigure($customer_id, $password, $new_password);
        if(!$result){
            // die("再設定に失敗しました。");
            $err_msgs = '再設定に失敗しました。';
            Common::sendErrorRedirect($err_msgs);
        }
        try {
            $this->view->display('password_reconfigure_result.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }
    

    // パスワードリマインダー
    // パスワードリセット(メールアドレス入力フォーム)
    public function viewPasswordReset(){
        @session_start();
        if(isset($_SESSION['customer_id'])&&isset($_SESSION['nickname'])){
            header('location:/');
        }
        // Common::removeAuthSession();
        $err_msgs = Common::getErrorMsgs();

        $key = Common::getCsrfKey();
        $_SESSION['key'] = $key;

        try {
            $this->view->assign(compact('err_msgs', 'key'));
            $this->view->display('password_reset_form.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }
    // パスワードリセットメール送信&送信画面表示
    public function passwordResetResult(){
        @session_start();
        Common::checkCsrfKey();
        $to = Common::trimSpace(htmlspecialchars($_POST['email_address']));
        $err_msgs = Validator::validate(
            array(
                'email_address' => $to
            )
        );

        if (!empty($err_msgs['email_address'])) {
            @session_start();
            $_SESSION['err_msgs'] = $err_msgs;
            // var_dump($_SESSION['err_msgs']);
            Common::sendRedirect($_SERVER['REQUEST_URI']);
            exit();
        }
        $result = $this->model->selectMailAndWdByEmail($to);
        $email_address = $result['email_address'];
        $created_at = $result['created_at'];
        $withdrawed_at = $result['withdrawed_at'];
        if($withdrawed_at != NULL){
            $err_msgs = '退会済みメールアドレスです。';
            Common::sendErrorRedirect($err_msgs);
            // array_push($err_msgs, '退会済みメールアドレスです。');
        }
        if(empty($email_address) || $created_at == NULL){
            $err_msgs = '登録されてないメールアドレスです。';
            Common::sendErrorRedirect($err_msgs);
        }else{
            if(!Customer::lockedUserByEmail($to)){
                $err_msgs = 'アカウントはロックされています。';
                Common::sendErrorRedirect($err_msgs);
            }    
        }
        // パスワードトークン生成&INSERT
        $id = $this->model->passwordTokenInsert($to);
        if(!$id){
            $err_msgs = '失敗しました。';
            Common::sendErrorRedirect($err_msgs);
        }
        // メール送信
        $this->mail->passwordResetSendMail($to);
        // var_dump($id);
        try {
            $this->view->assign('id', $id);
            $this->view->display('password_reset_result.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }
    // パスワードリセット(新しいパスワード入力画面)
    public function viewNewPassword(){
        @session_start();
        $err_msgs = Common::getErrorMsgs();

        $key = Common::getCsrfKey();
        $_SESSION['key'] = $key;

        // $id = htmlspecialchars($_GET['id']);
        if(!isset($_GET['email'])){
            header('location:/');
            exit();
        }
        $email_address = htmlspecialchars($_GET['email']);
        if (!$email_address) {
            header('location:/passreset');
            exit();
        }
        $this->model->passwordTokenCheck($email_address, $err_msgs ,$key);
    }
    // パスワードリセット処理
    public function newPasswordResult(){   
        @session_start();
        Common::checkCsrfKey();
        $email_address = $_SESSION['email_address'];

        if(!$email_address){
            header('location:/passreset');
            exit();
        }
        $password = Common::trimSpace(htmlspecialchars($_POST['password']));
        $password_confirm = Common::trimSpace(htmlspecialchars($_POST['password_confirm']));
        $id = Common::trimSpace(htmlspecialchars($_POST['id']));

        $err_msgs = Validator::validate(
            array(
                'password' => $password,
                'password_confirm' => $password_confirm,
                'id' => $id
            )
        );

        if (empty($id)) {
            $err_msgs['id'][0] =  '認証コードが空欄です。';
        }

        if (!empty($err_msgs['password']) || !empty($err_msgs['password_confirm']) || !empty($err_msgs['id'])){
            @session_start();
            $_SESSION['err_msgs'] = $err_msgs;
            // var_dump($_SESSION['err_msgs']);
            $url = "/newpass/?email=".$email_address;
            header('location:'.$url);
            exit();
        }

        $this->model->removeEmailAndIdSession();
        $this->model->newPasswordSet($email_address, $password, $id);
    }

    // ログイン画面
    public function getLogin(){
        @session_start();
        //最初にログアウト
        if(isset($_SESSION['customer_id'])&&isset($_SESSION['nickname'])){
            header('location:/');
        }

        //エラーを取得
        $err_msgs = Common::getErrorMsgs();

        $key = Common::getCsrfKey();
        // var_dump($key);
        $_SESSION['key'] = $key;

        //View
        $this->view->assign(compact('err_msgs','key'));
        $this->view->display('login.tpl');
    }
    // ログイン処理
    public function postLogin(){
        @session_start();
        Common::checkCsrfKey();

        //emailとPASSWORDを取得
        $email_address = Common::trimSpace(htmlspecialchars($_POST['email_address']));
        $password = Common::trimSpace(htmlspecialchars($_POST['password']));

        $err_msgs = Validator::validate(
            array(
                'email_address' => $email_address,
                'password' => $password
            )
        );

        if (!empty($err_msgs['email_address']) || !empty($err_msgs['password'])) {
            @session_start();
            $_SESSION['err_msgs'] = $err_msgs;
            // var_dump($_SESSION['err_msgs']);
            Common::sendRedirect($_SERVER['REQUEST_URI']);
            exit();
        }

        $result_logindata = $this->model->getLogindataByEmail($email_address);

        //認証
        $this->model->loginCheck($result_logindata, $password);

    }

    // ログアウト処理
    public function logout(){
        @session_start();
        if(!empty($_SESSION['customer_id'])){
            Common::removeAuthSession();
            @session_start();
            $err_msg = 'ログアウトしました。';
            Common::sendErrorRedirect($err_msg);
            exit();
        }else{
            Common::removeAuthSession();
            @session_start();
            $err_msg = 'ログインされていません。';
            Common::sendErrorRedirect($err_msg);
            exit();
        }
    }

    // マイページ遷移
    public function mypage(){
        @session_start();
        Common::checkLoginSession();
        // var_dump($_SESSION['customer_id']);
        $nickname = $_SESSION['nickname'];
        $this->view->assign('nickname', $nickname);
        try {
            $this->view->display('mypage.tpl');
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }
}