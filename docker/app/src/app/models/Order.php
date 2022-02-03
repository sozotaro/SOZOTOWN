<?php
namespace App\Model;

use App\libs\Common;
use PDO;
use PDOException;

class Order
{
    private $db;
    private $cart;
    private $order_items_limit  = PAGINATE_ORDER;

    //DB接続
    public function __construct() {
        $this->db = new DB();
        $this->cart = new Cart();
    }

    /**
     * 注文情報を登録する
     */
    public function orderRegistration($carts){
        if(isset($_SESSION)){
            $customer_id = $_SESSION['customer_id'];
            $subtotal = $_SESSION['subtotal'];
            $tax = $_SESSION['tax'];
            $charge = $_SESSION['charge'];
            $amount = $_SESSION['amount'];
            if($subtotal <= 0 || $tax <= 0 || $charge <= 0 || $amount <= 0){
                die('金額が正しくありません');
            }
            $this->checkProductStock($customer_id);

        }

        try{
            // トランザクション開始
            $this->db->beginTransaction();
            // 1.受注履歴テーブルに登録する
            $a_sql = 'INSERT INTO orders
                    (customer_id, subtotal, consumption_tax, charge, amount, status_id, name, name_kana, telephone_number,
                          post_number, area_id, address)
                    VALUES
                    (:customer_id, :subtotal, :tax, :charge, :amount, :status_id, :name, :name_kana, :telephone_number,
                          :post_number, :area_id, :address)';
            $_SESSION['post_number'] = str_replace('-','',$_SESSION['post_number']);
            $_SESSION['telephone_number'] = str_replace('-','',$_SESSION['telephone_number']);
            $stmt = $this->db->prepare($a_sql);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':subtotal', $subtotal, PDO::PARAM_INT);
            $stmt->bindParam('tax', $tax, PDO::PARAM_INT);
            $stmt->bindParam(':charge', $charge, PDO::PARAM_INT);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
            $stmt->bindValue(':status_id', 1, PDO::PARAM_INT);
            $stmt->bindParam(':name',$_SESSION['name']);
            $stmt->bindParam(':name_kana', $_SESSION['name_kana']);
            $stmt->bindParam(':telephone_number', $_SESSION['telephone_number']);
            $stmt->bindParam('post_number',$_SESSION['post_number']);
            $stmt->bindParam(':area_id',$_SESSION['pref']);
            $stmt->bindParam(':address', $_SESSION['address']);
            $stmt->execute() ?: die('登録処理に失敗しました');

            // 2.ordersテーブルの登録IDを取得する
            // $order_id = (int) $this->getOrderId($customer_id)['order_histories_id'];
            $order_id = $this->db->lastInsertId();

            // 3.受注詳細テーブルに登録する
            $b_sql = 'INSERT INTO order_details 
                    (order_histories_id, sku_id, order_quantity) 
                    VALUES
                    (:order_histories_id, :sku_id, :order_quantity)';
            // foreachでsku数分INSERTの処理
            foreach ($carts as $cart){
                $sku_id = (int) $cart['sku_id'];
                $quantity = (int) $cart['quantity'];

                $stmt = $this->db->prepare($b_sql);
                $stmt->bindParam(':order_histories_id',$order_id, PDO::PARAM_INT);
                $stmt->bindParam(':sku_id',$sku_id, PDO::PARAM_INT);
                $stmt->bindParam(':order_quantity',$quantity, PDO::PARAM_INT);
                $stmt->execute();
            }

            // 4.SKUの在庫を変更する
            $c_sql = 'UPDATE products_sku SET stock_quantity = stock_quantity - :quantity WHERE sku_id = :sku_id';
            foreach ($carts as $cart) {
                $stmt = $this->db->prepare($c_sql);
                $stmt->bindParam(':quantity',$cart['quantity']);
                $stmt->bindParam('sku_id',$cart['sku_id']);
                $stmt->execute();
            }
            // 5.カート内の商品を削除する(UPDATE)
            $this->cart->deleteCart($customer_id);

            // 6.deliveriesテーブルに注文者の情報がなければ注文時の注文者情報を登録する
            $delivery = (new Customer)->getDelivery($customer_id);

            if(!$delivery[1]){
                $d_sql = 'INSERT INTO deliveries 
                         (customer_id, name, name_kana, telephone_number, post_number, area_id, address)
                          VALUES
                         (:customer_id, :name, :name_kana, :telephone_number, :post_number, :area_id, :address)';
                $stmt = $this->db->prepare($d_sql);
                $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
                $stmt->bindParam(':name',$_SESSION['name']);
                $stmt->bindParam(':name_kana', $_SESSION['name_kana']);
                $stmt->bindParam(':telephone_number', $_SESSION['telephone_number']);
                $stmt->bindParam('post_number',$_SESSION['post_number']);
                $stmt->bindParam(':area_id',$_SESSION['pref']);
                $stmt->bindParam(':address', $_SESSION['address']);
                $stmt->execute();
            }


            // ここまで一連のトランザクションをコミット
            $this->db->commit();

            // セッションクリア
            

        }catch (PDOException $e) {
            // ロールバック処理
            $this->db->rollBack();
            error_log($e);
            var_dump($e);
        } finally {
            $this->db = null;
        }

    }

    /**
     * 注文履歴の一覧を取得する
     */
    public function getOrderList($p, $customer_id, $desc){
        $page = 0;
        
        if($p > 1){
            $page = 5*($p-1);
        }elseif($p == 1){
            $page = 0;
        }else{
            $page = 0;
        }
        // var_dump($page);
        $customer_id = (int)$customer_id;
        // var_dump($customer_id);
        $order_limit = (int)$this->order_items_limit;
        // var_dump($limit);
        try{

            //　履歴の一覧を取得する
            $a_sql = "SELECT
                        od.order_histories_id,
                        MIN(od.sku_id) as sku_id,
                        FORMAT(MAX(orders.amount) ,0) as amount,
                        COUNT(*) as count,
                        DATE_FORMAT(MAX(orders.ordered_at),'%Y/%m/%d') as ordered_at,
                        MAX(s.status_name) as status
                    FROM
                        orders
                            LEFT JOIN statuses s on orders.status_id = s.status_id
                            LEFT JOIN order_details od on orders.order_histories_id = od.order_histories_id
                            LEFT JOIN products_sku ps on od.sku_id = ps.sku_id
                            LEFT JOIN products p on ps.product_id = p.product_id
                    WHERE customer_id = :customer_id
                    GROUP BY od.order_histories_id
                    ORDER BY od.order_histories_id ".$desc.
                    " LIMIT :order_limit OFFSET :page ;";
            $stmt = $this->db->prepare($a_sql);
            $stmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_INT);
            $stmt->bindParam(':page', $page, \PDO::PARAM_INT);
            $stmt->bindParam(':order_limit', $order_limit, \PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();

            // sku_idの配列を取り出す
            $sku_int_array = array();
            $sku_array = array_column($results,'sku_id');

            foreach ($sku_array as $sku) {
                $sku_int_array[] = (int) $sku;
            }

            // SQLインジェクションの脆弱性あり、仕様について検討する必要あり
            // Ans.クエリから取得したデータを直接SQLに結合する場合であれば、この形でも概ね問題なしとのこと
            $b_sql = "SELECT
                        products.product_name
                      FROM
                        products
                        LEFT JOIN products_sku ps on products.product_id = ps.product_id
                        INNER JOIN order_details od on ps.sku_id = od.sku_id
                      WHERE ps.sku_id IN (".implode(',', $sku_int_array).")
                        ORDER BY order_detail_id ".$desc.";";
            $stmt = $this->db->prepare($b_sql);
            //$stmt->bindParam(':sku_int_array',$sku_int_implode_comma);
            $stmt->execute($sku_int_array);
            $results2 = $stmt->fetchAll();

            // 配列を結合する
            $i = 0;
            foreach ($results as $result){
                $result = array_merge($result,$results2[$i]);
                $i++;
                $orders[] = $result;
            }
        }catch(PDOException $e){
            error_log($e);
            // var_dump($e);
            return false;
        }
        return $orders;
    }

    /**
     * 注文履歴の詳細を表示する
     */
    public function getOrderDetail($order_id){
        try{
            $sql = 'SELECT
                        p.product_id,
                        p.product_name,
                        FORMAT(ps.price,0) as subtotal,
                        p.product_explanation,
                        s.size_name,
                        c.condition_name
                    FROM order_details
                        LEFT JOIN products_sku ps on order_details.sku_id = ps.sku_id
                        LEFT JOIN products p on ps.product_id = p.product_id
                        LEFT JOIN sizes s on ps.size_id = s.size_id
                        LEFT JOIN conditions c on ps.condition_id = c.condition_id
                    WHERE order_details.order_histories_id = :order_histories_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':order_histories_id',$order_id, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
        }catch(PDOException $e){
            error_log($e);
            return false;
        } finally {
            $this->db = null;
        }
        return $results;
    }


    /**
     * 都道府県のリストを取得する
     */
    public static function getPref(): array {

        return array(
            ''=>'選択してください',
            '1'=>'北海道',
            '2'=>'青森県',
            '3'=>'岩手県',
            '4'=>'宮城県',
            '5'=>'秋田県',
            '6'=>'山形県',
            '7'=>'福島県',
            '8'=>'茨城県',
            '9'=>'栃木県',
            '10'=>'群馬県',
            '11'=>'埼玉県',
            '12'=>'千葉県',
            '13'=>'東京都',
            '14'=>'神奈川県',
            '15'=>'新潟県',
            '16'=>'富山県',
            '17'=>'石川県',
            '18'=>'福井県',
            '19'=>'山梨県',
            '20'=>'長野県',
            '21'=>'岐阜県',
            '22'=>'静岡県',
            '23'=>'愛知県',
            '24'=>'三重県',
            '25'=>'滋賀県',
            '26'=>'京都府',
            '27'=>'大阪府',
            '28'=>'兵庫県',
            '29'=>'奈良県',
            '30'=>'和歌山県',
            '31'=>'鳥取県',
            '32'=>'島根県',
            '33'=>'岡山県',
            '34'=>'広島県',
            '35'=>'山口県',
            '36'=>'徳島県',
            '37'=>'香川県',
            '38'=>'愛媛県',
            '39'=>'高知県',
            '40'=>'福岡県',
            '41'=>'佐賀県',
            '42'=>'長崎県',
            '43'=>'熊本県',
            '44'=>'大分県',
            '45'=>'宮崎県',
            '46'=>'鹿児島県',
            '47'=>'沖縄県'
        );
    }

    /**
     * 都道府県名をidから取得する
     */
    public function getPrefById($id){
        try{
            $sql = 'SELECT area_name FROM areas WHERE area_id = :area_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':area_id', $id);
            $stmt->execute();
            $result = $stmt->fetch();
        }catch(PDOException $e){
            error_log($e);
        } finally {
           $this->db = null;
        }
        return $result['area_name'];
    }

    // セッション内のフォーム入力情報を破棄する
    public static function removeOrderSession(){

        if (isset($_SESSION['key'])){
            unset($_SESSION['key']);
        }
        if (isset($_SESSION['name'])){
            unset($_SESSION['name']);
        }
        if (isset($_SESSION['name_kana'])){
            unset($_SESSION['name_kana']);
        }
        if (isset($_SESSION['post_number'])){
            unset($_SESSION['post_number']);
        }
        if (isset($_SESSION['pref'])){
            unset($_SESSION['pref']);
        }
        if (isset($_SESSION['city'])){
            unset($_SESSION['city']);
        }
        if (isset($_SESSION['street'])){
            unset($_SESSION['street']);
        }
        if (isset($_SESSION['building'])){
            unset($_SESSION['building']);
        }
        if (isset($_SESSION['address'])){
            unset($_SESSION['address']);
        }
        if (isset($_SESSION['telephone_number'])){
            unset($_SESSION['telephone_number']);
        }
        if (isset($_SESSION['subtotal'])){
            unset($_SESSION['subtotal']);
        }
        if (isset($_SESSION['tax'])){
            unset($_SESSION['tax']);
        }
        if (isset($_SESSION['charge'])){
            unset($_SESSION['charge']);
        }
        if (isset($_SESSION['carts'])){
            unset($_SESSION['carts']);
        }
        if (isset($_SESSION['amount'])){
            unset($_SESSION['amount']);
        }
        if (isset($_SESSION['err_msgs'])){
            unset($_SESSION['err_msgs']);
        }

    }

    // 購入履歴数を取得する
    public function GetQuantityByOrderHistories($customer_id) {
        $customer_id = (int)$customer_id;
        // var_dump($customer_id);
        try {
            $sql = 'SELECT COUNT(order_histories_id) AS c
                    from orders
                    where customer_id = :customer_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id );
            $stmt->execute();
            $results = $stmt->fetchAll();
            $result = $results[0]["c"];
        } catch (PDOException $pe) {
            error_log($pe);
            return false;
        }
        return $result;
    }


    /**
     * カート内の在庫数量を確認する
     */
    public function checkProductStock($customer_id) {
        try{
            $sql = 'SELECT
                        COUNT(*) as c
                    FROM
                         products_sku
                    INNER JOIN carts c
                        ON products_sku.sku_id = c.sku_id
                    WHERE c.sku_id
                              IN (
                                  SELECT carts.sku_id FROM carts
                                  WHERE customer_id = :customer_id
                                  AND carts.deleted_at IS NULL
                              )
                    AND products_sku.stock_quantity <= 0
                    AND c.deleted_at IS NULL';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id);
            $stmt->execute();
            $count = $stmt->fetch();
            if( (int) $count['c'] != 0){
                $_SESSION['err_msgs']['count'] = '完売している商品が含まれています';
                Common::sendRedirect('/cart');
            }

        }catch (\PDOException $e){
            error_log($e);
        }

    }

    /**
     * 受注履歴IDを取得する
     */
//    public function getOrderId($customer_id){
//        try{
//            $sql = 'SELECT order_histories_id FROM orders
//                      WHERE customer_id = :customer_id ORDER BY ordered_at DESC LIMIT 1';
//            $stmt = $this->db->prepare($sql);
//            $stmt->bindParam(':customer_id',$customer_id);
//            $stmt->execute();
//            $order_id = $stmt->fetch();
//        }catch(\PDOException $e){
//            error_log($e);
//        }
//        return $order_id;
//    }

}