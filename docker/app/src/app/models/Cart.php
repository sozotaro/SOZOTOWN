<?php

namespace App\Model;


use PDO;
use PDOException;
use App\libs\Common;

class Cart
{
    private $db;

    //DB接続
    public function __construct() {
        $this->db = new DB();
    }

    // カートの商品を表示する
    public function getCartData() {
        Common::checkLoginSession();
        if(isset($_SESSION['customer_id'])){
            $customer_id = $_SESSION['customer_id'];
        }
        try {
            $sql = 'SELECT
                        carts.cart_id
                         , carts.customer_id
                         , carts.sku_id
                         , products.product_name
                         , products.product_explanation
                         , products_sku.price
                         , carts.quantity
                         , sizes.size_name
                         , conditions.condition_name
                         , carts.deleted_at
                    FROM carts
                        LEFT JOIN products_sku
                                  ON products_sku.sku_id = carts.sku_id
                        LEFT JOIN products
                                  ON products_sku.product_id = products.product_id
                        LEFT JOIN sizes
                                  ON products_sku.size_id = sizes.size_id
                        LEFT JOIN conditions
                                  ON products_sku.condition_id = conditions.condition_id
                    WHERE
                        carts.customer_id = :customer_id
                        AND
                        carts.deleted_at IS NULL;';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e);
            return false;
        } finally {
            $this->db = null;
        }
        return $results;
    }

    // カートに商品を追加する
    public function addCart(): bool {
        Common::checkLoginSession();
        $sku_id = (int)Common::trimSpace(htmlspecialchars($_POST['sku_id']));

        if(isset($_SESSION['customer_id'])){
            $customer_id = $_SESSION['customer_id'];
        }
        $quantity = 1;

        try {

            // カートの重複登録を確認する
            $this->checkDuplicateItem($sku_id);

            // 商品在庫の確認

            // カートの登録件数を取得
            // $cart_id = (int)$this->getRowCount() + 1;
            $sql = 'INSERT INTO carts (customer_id, sku_id, quantity)
                VALUES (:customer_id, :sku_id, :quantity)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':sku_id', $sku_id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $result = $stmt->execute();
        } catch (PDOException $e) {
            error_log($e);
            return false;
        } finally {
            $this->db = null;
        }
        return $result;
    }

    // カートの数量を変更する
//    public function changeCartQuantity($cart_id): bool {

//        Common::checkLoginSession();
//        $cart_id = htmlspecialchars($cart_id);
//        $quantity = htmlspecialchars($_POST['quantity']);
//        try {
//            // UPDATE
//            $sql = 'UPDATE carts SET quantity = :quantity WHERE cart_id = :cart_id';
//            $stmt = $this->db->prepare($sql);
//            $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
//            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
//            $result = $stmt->execute();
//        } catch (PDOException $e) {
//            error_log($e);
//            return false;
//        } finally {
//            $this->db = null;
//        }
//        return $result;
//     }

    // カートの商品を削除する
    public function delCartItem($cart_id): bool {
        Common::checkLoginSession();
        $cart_id = (int)Common::trimSpace(htmlspecialchars($cart_id));
        // 顧客IDチェック
        if(isset($_SESSION['customer_id'])){
            $customer_id = $_SESSION['customer_id'];
        }

        try {
            $sql = 'UPDATE carts SET deleted_at = CURRENT_TIMESTAMP WHERE cart_id = :cart_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
            $result = $stmt->execute();
        } catch (PDOException $e) {
            // var_dump($e);
            error_log($e);
            return false;
        } finally {
            $this->db = null;
        }
        return $result;
    }

    // カートからすべての商品を削除する
    public function deleteCart($customer_id): bool {
        Common::checkLoginSession();
        try {
            $sql = 'UPDATE carts SET deleted_at = CURRENT_TIMESTAMP WHERE customer_id = :customer_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id',$customer_id, PDO::PARAM_INT);
            $result = $stmt->execute();
        } catch (PDOException $e) {
            error_log($e);
            return false;
        }
        return $result;
    }

    // カートのレコード数を取得する
    public function getRowCount() {
        try {
            $sql = 'SELECT COUNT(*) as count FROM carts';
            $stmt = $this->db->query($sql);
            $count = $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e);
            return false;
        }
        return $count['count'];
    }

    // 重複商品の登録がないか検査する
    public function checkDuplicateItem($sku_id) {
        if(isset($_SESSION['customer_id'])){
            $customer_id = (int) $_SESSION['customer_id'];
        }
        try {
            $sql = 'SELECT COUNT(*) as c FROM carts 
                    WHERE sku_id = :sku_id AND customer_id = :customer_id
                    AND
                    deleted_at IS NULL';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':sku_id', $sku_id, PDO::PARAM_INT);
            $stmt->bindParam('customer_id',$customer_id,PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetch();
            // var_dump($count['c']);
        } catch (PDOException $e) {
            error_log($e);
            return false;
        }
        if ((int)$count['c'] > 0) {
            $_SESSION['err_msgs']['csrf'] = '同一商品の登録はできません';
            Common::sendRedirect('/products');
            // var_dump($_SESSION['err_msgs']);
            exit();
        }
        return $count;
    }

}