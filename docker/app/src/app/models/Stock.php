<?php
namespace App\Model;

class Stock
{
//    private $db;
//    private $table = 'stocks';
//
//    //DB接続
//    public function __construct()
//    {
//        $this->db = new DB();
//    }
//
//    // 全在庫情報の取得
//    public function getAllStocks()
//    {
//        try {
//            $sql = sprintf('SELECT * FROM %s', $this->table);
//            $stmt = $this->db->query($sql);
//            $stmt->execute();
//            $results = $stmt->fetchAll();
//        } catch (\PDOException $pe) {
//            error_log($pe);
//            return false;
//
//        }
//        $this->db = NULL;
//        return $results;
//    }
//
//    // 新しい在庫を登録する
//    public function createStock(){
//
//        $product_id = htmlspecialchars($_POST['product_name']);
//        $size_id = htmlspecialchars($_POST['category_id']);
//        $condition_id = htmlspecialchars($_POST['condition_id']);
//        $price = '金額'; //priceの計算ロジックは別途関数で定義する
//        $quantity = htmlspecialchars($_POST['quantity']);
//        try{
//            // INSERT INTO ---
//            $sql = 'INSERT INTO stocks (product_id, size_id, condition_id, price, stock_quantity)
//                    VALUES (:product_id, :size_id, :condition_id, :price, :stock_quantity)';
//            $stmt = $this->db->prepare($sql);
//            $stmt->bindParam(':product_id',$product_id,\PDO::PARAM_INT);
//            $stmt->bindParam(':size_id',$size_id,\PDO::PARAM_INT);
//            $stmt->bindParam(':condition_id',$condition_id,\PDO::PARAM_INT);
//            $stmt->bindParam(':price',$price,\PDO::PARAM_INT);
//            $stmt->bindParam('stock_quantity',$quantity,\PDO::PARAM_INT);
//            $result = $stmt->execute();
//        }catch(\PDOException $e){
//            error_log($e);
//            return false;
//        }
//        $this->db = NULL;
//        return $result;
//    }
}