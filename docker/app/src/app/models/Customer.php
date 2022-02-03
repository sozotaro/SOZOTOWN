<?php
namespace App\Model;

use App\Controller\CustomerController;
use Dotenv\Dotenv;
use App\libs\Common;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

define("TEMP_REGISTER_TIME", $_ENV['TEMP_REGISTER_TIME']);
define("RESET_PASS_TIME", $_ENV['RESET_PASS_TIME']);

class Customer
{
    private $db;
    private $name = '';
    private $temp_register_time  = TEMP_REGISTER_TIME;
    private $reset_pass_time  = RESET_PASS_TIME;

    //DB接続
    public function __construct()
    {
        $this->view = new \Smarty();
        $this->db = new DB();
    }

    
    // セッション破棄(仮登録)
    public function removeUserinfoSession(){
        @session_start();
        if(isset($_SESSION['user_info'])){
            unset($_SESSION['user_info']);
        }
    }
    // セッション破棄(メールアドレス,トークンID)
    public static function removeEmailAndIdSession(){
        @session_start();
        if(isset($_SESSION['id'])){
            unset($_SESSION['id']);
        }
        if(isset($_SESSION['email_address'])){
            unset($_SESSION['email_address']);
        }
    }
    // セッション登録
    public static function setAuthSession($customer_id, $nickname){
        @session_start();
        session_regenerate_id(true);
        $_SESSION['customer_id'] = $customer_id;
        $_SESSION['nickname'] = $nickname;
    }
    
        // メールアドレス重複確認
    public function countEmail($to){
        try{
            $sql = 'SELECT COUNT(*) as count FROM customers WHERE email_address = :email_address';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email_address',$to, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
            $count = (int)$result['count'];
            // var_dump($count);
        }catch(\PDOException $e){
            // var_dump($e);
            $this->db = NULL;
            return false;
        }
        if ( $count >= 1){
            Common::removeAuthSession();
            $this->db = null;
            @session_start();
            $err_msgs = array();
            $err_msgs['email_address'][0] = 'メールアドレスが重複しています。';
            $_SESSION['err_msgs'] = $err_msgs;
            Common::sendRedirect($_SERVER['REQUEST_URI']);
            exit();
        }
        return $result;
    }
    //顧客テーブル&仮登録テーブルにINSERT
    public function customerInsert($to, $password, $nickname, $id, $top_secret){
        try{
            $this->db->beginTransaction();
            // INSERT INTO ---


            $sql = 'INSERT INTO customers(email_address, password, nickname) VALUES (:email_address, :password, :nickname)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email_address',$to, \PDO::PARAM_STR);
            $stmt->bindParam(':password',$password, \PDO::PARAM_STR);
            $stmt->bindParam(':nickname',$nickname, \PDO::PARAM_STR);
            $result = $stmt->execute();

            $sql = 'INSERT INTO temporary_registrations(customer_id, access_token, password_token) 
            VALUES ((SELECT customer_id FROM customers WHERE email_address = :email_address), :access_token, :password_token)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email_address',$to, \PDO::PARAM_STR);
            $stmt->bindParam(':access_token',$id, \PDO::PARAM_STR);
            $stmt->bindParam(':password_token',$top_secret, \PDO::PARAM_STR);
            $result = $stmt->execute();

            $this->db->commit();
        }catch(\PDOException $e){
            $this->db->rollBack();
            // var_dump($e);
            return false;
        }finally{
            $this->db = NULL;
        }

        return $result;
    }

    //仮登録データ取得
    public function tempoRegisSelect($id){
        try{
            $this->db->beginTransaction();

            $sql='SELECT customer_id, temporary_registration_at FROM temporary_registrations WHERE access_token = :id AND temporary_registration_at IS NOT NULL';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_STR);
            $stmt->execute();
            $result_tmpo = $stmt->fetch();
            $customer_id = $result_tmpo['customer_id'];

            $sql = 'SELECT password FROM customers WHERE customer_id = :customer_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_INT);
            $stmt->execute();
            $result_pass = $stmt->fetch();

            $results = array($result_tmpo, $result_pass);
            $this->db->commit();
        }catch(\PDOException $e){
            error_log($e);
            $this->db = NULL;
            return false;
        }
        return $results;
    }
    public function dbNull(){
        $this->db = NULL;
    }

    // 会員登録処理&ニックネーム取得
    public function entryDateAndSelectNickname($customer_id){
        try{
            $this->db->beginTransaction();

            $sql = "UPDATE customers SET created_at = current_timestamp WHERE customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE temporary_registrations SET temporary_registration_at = NULL WHERE  customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_INT);
            $stmt->execute();

            $sql = "SELECT nickname FROM customers WHERE customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();

            $this->db->commit();
        }catch(\PDOException $e){
            $this->db->rollBack();
            // var_dump($e);
            return false;
        }finally{
            $this->db = NULL;
        }
        return $result['nickname'];
    }

    //メールアドレスからログインデータ取得
    public function getLogindataByEmail(string $email){
        try{
            $sql = "SELECT customer_id, password, nickname, password_token, reseted_at, created_at, withdrawed_at FROM customers WHERE email_address = :mail";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':mail',$email,\PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $this->db = NULL;
        }
        return $result;
    }

    // ログインエラーINSERT
   public static function createLoginFails($customer_id){
        try{
            $dbset = new DB();
            $sql = 'INSERT INTO login_errors(customer_id, login_error_history_at) VALUES (:customer_id, current_timestamp)';
            $stmt = $dbset->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $result = $stmt->execute();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $dbset = NULL;
        }
        return $result;
    }
    // ログインロックカウント
    public static function isValidUser($customer_id){
        try{
            $dbset = new DB();
            $sql = 'select count(*) AS count from login_errors WHERE customer_id = :customer_id AND login_error_history_at > CURRENT_TIMESTAMP + INTERVAL -3 MINUTE';
            $stmt = $dbset->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $dbset = NULL;
        }
        if($result['count'] >= 3){
            Customer::insertLocked($customer_id);
            Customer::clearFails($customer_id);
            $result = false;
        }else{
            $result = true;
        }
        return $result;
    }
    // ログインロック処理
    public static function insertLocked($customer_id){
        try{
            $dbset = new DB();
            $sql = 'UPDATE customers SET password_locked_at = current_timestamp WHERE customer_id = :customer_id';
            $stmt = $dbset->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $stmt->execute();
            $stmt->fetch();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $dbset = NULL;
        }
    }
    // ログインロック確認
    public static function lockedUser($customer_id){
        try{
            $dbset = new DB();
            $sql = 'select customer_id from customers WHERE customer_id = :customer_id AND (password_locked_at < CURRENT_TIMESTAMP + INTERVAL -3 MINUTE OR password_locked_at IS NULL)';
            $stmt = $dbset->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            // var_dump($result);
            // exit();
            if($result['customer_id']){
                $result = true;
            }
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $dbset = NULL;
        }
        return $result;
    }
    
    // ログインエラー消去
    public static function clearFails($customer_id){
        try{
            $dbset = new DB();
            $sql = 'delete from login_errors where customer_id = :customer_id';
            $stmt = $dbset->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $result = $stmt->execute();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $dbset = NULL;
        }
        return $result;
    }
    // ロック日時をNULLにする
    public static function lockedNULL($customer_id){
        try{
            $dbset = new DB();
            $sql = 'UPDATE customers SET password_locked_at = NULL WHERE customer_id = :customer_id';
            $stmt = $dbset->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $stmt->execute();
            $stmt->fetch();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $dbset = NULL;
        }
    }

    //ログイン認証
    public function loginCheck($result_logindata, $password){
        $auth_password = $result_logindata['password'];
        $created_at = $result_logindata['created_at'];
        $withdrawed_at = $result_logindata['withdrawed_at'];
        $customer_id = $result_logindata['customer_id'];
        $nickname = $result_logindata['nickname'];
        $password_token = $result_logindata['password_token'];
        $reseted_at = $result_logindata['reseted_at']; 
        if($password_token != NULL || $reseted_at != NULL){
            Customer::passwordResetNULL($customer_id);
        }
        if($created_at != NULL){
            if($withdrawed_at == NULL){
                if(!Customer::isValidUser($customer_id)){
                    $err_msgs = 'アカウントはロックされています';
                    Common::sendErrorRedirect($err_msgs);
                }
                if(!Customer::lockedUser($customer_id)){
                    $err_msgs = 'アカウントはロックされています';
                    Common::sendErrorRedirect($err_msgs);
                }
                if (password_verify($password, $auth_password)) {
                    Customer::lockedNULL($customer_id);
                    Customer::clearFails($customer_id);
                    Customer::setAuthSession($customer_id, $nickname);

                    header('location:/');
                    
                } else {
                    Customer::createLoginFails($customer_id);
                    $err_msgs = 'ログインに失敗しました';
                    Common::sendErrorRedirect($err_msgs);
                }
            }else{
                $err_msgs = '退会済みです。';
                Common::sendErrorRedirect($err_msgs);
            }
        }else{
            $err_msgs = '登録されていません。';
            Common::sendErrorRedirect($err_msgs);
        }
    }

    // メールアドレス取得
    public function getEmail($customer_id){
        try{
            $sql = "SELECT email_address FROM customers WHERE customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $this->db = NULL;
        }
        return $result;
    }

    // 会員情報取得
    public function getDelivery($customer_id){
        try{
            $this->db->beginTransaction();

            $sql = "SELECT email_address FROM customers WHERE customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $stmt->execute();
            $result_email = $stmt->fetch();

            $sql = "SELECT * FROM  deliveries WHERE customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $stmt->execute();
            $result_delivery = $stmt->fetch();
            
            if($result_delivery['address']){
                $result_address = explode(",", $result_delivery['address']);
            }else{
                $result_address = array('','','');
            }
            $results = array($result_email, $result_delivery, $result_address);

            $this->db->commit();
        }catch(\PDOException $e){
            $this->db->rollBack();
            error_log($e);
            return false;
        }finally{
            $this->db = NULL;
        }
        return $results;
    }
    // customers更新処理
    public function updateCustomer($nickname, $email_address, $customer_id){
        try{
            $this->db->beginTransaction();

            $sql = 'SELECT COUNT(*) as count FROM customers WHERE email_address = :email_address AND customer_id != :customer_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email_address',$email_address, \PDO::PARAM_STR);
            $stmt->bindParam(':customer_id',$customer_id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $count = (int)$result['count'];
            // var_dump($count);
            // exit();

            if($count >= 1){
                $this->db = NULL;
                $err_msgs = array();
                $err_msgs['email_address'][0] = 'メールアドレスが重複しています。';
                @session_start();
                $_SESSION['err_msgs'] = $err_msgs;
                header('location:/userinfo');
                exit();
            }else{
                $sql = "UPDATE customers SET email_address = :email_address, nickname = :nickname WHERE customer_id = :customer_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':nickname', $nickname, \PDO::PARAM_STR);
                $stmt->bindParam(':email_address', $email_address, \PDO::PARAM_STR);
                $stmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_INT);
                $stmt->execute();
    
                $sql = "SELECT nickname FROM customers WHERE customer_id = :customer_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch();
            }
            // var_dump($result);
            // exit();
            $this->db->commit();

        }catch(\PDOException $e){
            $this->db->rollBack();
            error_log($e);
            return false; 
        }finally{
            $this->db = NULL;
        }
        return $result['nickname'];
    }
    // deliveries更新処理
    public function updateDelivery($delivery){
        $name = $delivery['name'];
        $name_kana = $delivery['name_kana'];
        $post_number = str_replace('-', '', $delivery['post_number']); 
        $area_id = $delivery['area_id'];
        $address = $delivery['address'];
        $telephone_number = str_replace('-', '', $delivery['telephone_number']); 
        $customer_id = $delivery['customer_id'];
        try{ 
            $this->db->beginTransaction();

            $sql = 'SELECT COUNT(*) as count FROM deliveries WHERE customer_id = :customer_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $count = intval($result['count']);
            // var_dump($count);

            if($count >= 1){
                $sql = "UPDATE deliveries SET 
                name = :name, 
                name_kana = :name_kana, 
                telephone_number = :telephone_number, 
                post_number =:post_number, 
                area_id = :area_id, 
                address = :address 
                WHERE customer_id = :customer_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
                $stmt->bindParam(':name_kana', $name_kana, \PDO::PARAM_STR);
                $stmt->bindParam(':post_number', $post_number, \PDO::PARAM_STR);
                $stmt->bindParam(':area_id', $area_id, \PDO::PARAM_INT);
                $stmt->bindParam(':address', $address, \PDO::PARAM_STR);
                $stmt->bindParam(':telephone_number', $telephone_number, \PDO::PARAM_STR);
                $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
                $result = $stmt->execute();
            }else{
                $sql = "INSERT INTO deliveries
                (customer_id, name, name_kana, telephone_number, post_number, area_id, address)
                VALUES (:customer_id, :name, :name_kana, :telephone_number, :post_number, :area_id, :address)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
                $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
                $stmt->bindParam(':name_kana', $name_kana, \PDO::PARAM_STR);
                $stmt->bindParam(':post_number', $post_number, \PDO::PARAM_STR);
                $stmt->bindParam(':area_id', $area_id, \PDO::PARAM_INT);
                $stmt->bindParam(':address', $address, \PDO::PARAM_STR);
                $stmt->bindParam(':telephone_number', $telephone_number, \PDO::PARAM_STR);
                $result = $stmt->execute();
            }
            $this->db->commit();
        }catch(\PDOException $e){
            $this->db->rollBack();
            // var_dump($e);
            return false; 
        }finally{
            $this->db = NULL;
        }
        return $result;
    }
     
    //退会処理
    public function withdrawedUpdate($customer_id){
        try{
            $this->db->beginTransaction();

            $sql = "UPDATE customers SET withdrawed_at = current_timestamp WHERE customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_INT);
            $stmt->execute();

            $sql = "SELECT email_address FROM customers WHERE customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();

            $this->db->commit();
        }catch(\PDOException $e){
            $this->db->rollBack();
            error_log($e);
            return false; 
        }finally{
            $this->db = NULL;
        }
        return (!empty($result)) ? $result['email_address']: NULL;
    }
    // パスワード再設定
    public function setPasswordReconfigure($customer_id, $password, $new_password){
        try{
            $this->db->beginTransaction();
            $sql = "SELECT password FROM customers WHERE customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id,\PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $selectPassword = $result['password'];

            $err_msgs = array();
            if(!empty($password)&&!password_verify($password, $selectPassword)){
                $err_msgs['password'][0] = '現在のパスワードが違います。';
            }
            if (count($err_msgs)) {
                @session_start();
                $_SESSION['err_msgs'] = $err_msgs;
                Common::sendRedirect($_SERVER['REQUEST_URI']);
                exit();
            }
            $hashnewpass = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE customers SET password = :hashnewpass WHERE customer_id = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_INT);
            $stmt->bindParam(':hashnewpass', $hashnewpass, \PDO::PARAM_STR);
            $result = $stmt->execute();

            $this->db->commit();
        }catch(\PDOException $e){
            $this->db->rollBack();
            error_log($e);
            return false; 
        }finally{
            $this->db = NULL;
        }
        return $result;
    }

    // メールアドレスと退会情報と登録日時取得
    public function selectMailAndWdByEmail($email_address){
        try{
            $sql = "SELECT email_address, created_at, withdrawed_at FROM customers WHERE email_address = :email_address";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email_address',$email_address,\PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
        }catch(\PDOException $e){
            error_log($e);
            $this->db = NULL;
            return false; 
        }
        return $result;
    }
    // パスワードトークン生成&INSERT
    public function passwordTokenInsert($to){
        // パスワードトークン作成
        $alp = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz0123456789';
        $id = substr(str_shuffle($alp), 0, 8);
        $password_token = password_hash($id, PASSWORD_DEFAULT);

        try{
            $sql = "UPDATE customers SET password_token = :password_token, reseted_at = current_timestamp WHERE email_address = :email_address";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password_token', $password_token, \PDO::PARAM_STR);
            $stmt->bindParam(':email_address', $to, \PDO::PARAM_STR);
            $stmt->execute();
        }catch(\PDOException $e){
            error_log($e);
            return false; 
        }finally{
            $this->db = NULL;
        }
        return $id;
    }

    // ログインロック確認(リセット時)
    public static function lockedUserByEmail($to){
        try{
            $dbset = new DB();
            $sql = 'select customer_id from customers WHERE email_address = :email_address AND (password_locked_at < CURRENT_TIMESTAMP + INTERVAL -3 MINUTE OR password_locked_at IS NULL)';
            $stmt = $dbset->prepare($sql);
            $stmt->bindParam(':email_address',$to,\PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
            // var_dump($result);
            // exit();
            if($result['customer_id']){
                $result = true;
            }else{
                $result = false;
            }
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $dbset = NULL;
        }
        // var_dump($result);
        // exit();
        return $result;
    }
    // パスワードトークンチェック
    public function passwordTokenCheck($email_address, $err_msgs, $key){
        // $token = $id.$email_address;
        try{
            $sql = "SELECT reseted_at FROM customers  WHERE email_address = :email_address";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email_address', $email_address, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
            $reseted_at = $result['reseted_at'];
        }catch(\PDOException $e){
            error_log($e);
            return false; 
        }finally{
            $this->db = NULL;
        }
        if(!$result){
            $err_msg = '無効なアドレスです。';
            Common::sendErrorRedirect($err_msg);
        }
        if($reseted_at != NULL){
                @session_start();
                $_SESSION['email_address'] = $email_address;
                try {
                    $this->view->assign(compact('err_msgs','key'));
                    $this->view->display('new_password_form.tpl');
                    exit();
                } catch (\SmartyException $e) {
                    error_log($e);
                }
        }else{
            $err_msg = '失効済みURLです。';
            Common::sendErrorRedirect($err_msg);
        }
    }

    // パスワードリセット情報をNULL
    public static function passwordResetNULL($customer_id){
        try{
            $dbset = new DB();
            $sql = "UPDATE customers SET password_token = NULL, reseted_at = NULL WHERE customer_id = :customer_id";
            $stmt = $dbset->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_STR);
            $result = $stmt->execute();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }finally{
            $dbset = NULL;
        }
        return $result;
    }
    // パスワード再設定(リセット)
    public function newPasswordSet($email_address, $password, $id){
        $newpass = password_hash($password, PASSWORD_DEFAULT);
        
        try{
            $this->db->beginTransaction();

            $sql = "SELECT customer_id, password_token FROM customers WHERE email_address = :email_address";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email_address', $email_address, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
            $customer_id = $result['customer_id'];
            $password_token = $result['password_token'];

            if(!Customer::isValidUser($customer_id)){
                Customer::passwordResetNULL($customer_id);
                Customer::clearFails($customer_id);
                $this->db = NULL;
                @session_start();
                $err_msg = '最初からやり直してください。';
                Common::sendErrorRedirect($err_msg);
                exit();
            }
            if (!password_verify($id, $password_token)){
                Customer::createLoginFails($customer_id);
                $this->db = NULL;
                @session_start();
                $err_msgs = array();
                $err_msgs['id'][0] = '認証コードが違います。';
                $_SESSION['err_msgs'] = $err_msgs;
                $url = "/newpass/?email=".$email_address;
                header('location:'.$url);
                exit();
            }
            
            $sql = "UPDATE customers SET password = :newpass, password_token = NULL, reseted_at = NULL WHERE email_address = :email_address";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':newpass', $newpass, \PDO::PARAM_STR);
            $stmt->bindParam(':email_address', $email_address, \PDO::PARAM_STR);
            $result = $stmt->execute();

            $this->db->commit();
        }catch(\PDOException $e){
            $this->db->rollBack();
            error_log($e);
            $result = false;
            // return $result; 
        }finally{
            $this->db = NULL;
        }
        if($result === false){
            $err_msg = '失敗しました。';
            Common::sendErrorRedirect($err_msg);
        }
        try {
            $this->view->display('new_password_set.tpl');
            exit();
        } catch (\SmartyException $e) {
            error_log($e);
        }
    }

}