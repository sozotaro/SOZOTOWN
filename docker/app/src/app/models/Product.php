<?php

namespace App\Model;


use App\libs\Common;
use PDO;
use PDOException;

class Product
{
    private $db;
    private $table = 'products';
    private $items_limit  = PAGINATE_ITEMS;

    //DB接続
    public function __construct() {
        $this->db = new DB();
    }

    // 商品一覧を取得する
    public function getAllProducts() {
        try {
            $sql = sprintf('SELECT * FROM %s', $this->table);
            $stmt = $this->db->query($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
        } catch (PDOException $pe) {
            error_log($pe);
            return false;
        }
        $this->db = null;
        return $results;
    }

    // 商品詳細を取得する
    public function getProduct($param) {
        try {
            $int_id = (int) $param['id'];
            $sql = 'SELECT
                        products_sku.sku_id
                        , products.product_name
                        , products.product_id
                        , products.category_id
                        , products.product_explanation
                        , products_sku.price
                        , sizes.size_name
                        , conditions.condition_name
                        , products_sku.stock_quantity
                    FROM
                        products_sku 
                        LEFT JOIN products 
                            ON products_sku.product_id = products.product_id
                        LEFT JOIN sizes
                            ON products_sku.size_id = sizes.size_id
                        LEFT JOIN conditions
                            ON products_sku.condition_id = conditions.condition_id
                    WHERE
                        products.product_id = :product_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':product_id', $int_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($stmt->rowCount() == 0) {
                echo "<script>alert('検索結果はありません')</script>";
            }
        } catch (PDOException $pe) {
            error_log($pe);
            return false;
        }
        return $result;
    }

    // 商品を検索する
    public function searchProducts($item) {
        $item = Common::trimSpace(htmlspecialchars($item));
        try {
            $item = '%' . $item . '%';
            $sql = 'SELECT * FROM products WHERE product_name LIKE :product_name';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':product_name', $item);
            $stmt->execute();
            $results = $stmt->fetchAll();
            var_dump('検索結果: ' . $stmt->rowCount() . '件');
        } catch (PDOException $e) {
            error_log($e);
            return false;
        }
        $this->db = null;
        return $results;
    }

    // 商品の最低価格を取得する
    public function getMinimumPrice($product_id){
        $id = (int) $product_id;
        try {
            $sql = 'SELECT MIN(price) as min FROM products_sku WHERE product_id = :product_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':product_id',$id);
            $stmt->execute();
            $minimum = $stmt->fetch();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }
        return $minimum['min'];
    }

    // 新しい商品マスタを登録する
    public function createProduct() {
        $name = Common::trimSpace(htmlspecialchars($_POST['product_name']));
        $id = Common::trimSpace(htmlspecialchars($_POST['category_id']));
        $explanation = Common::trimSpace(htmlspecialchars($_POST['product_explanation']));
        $image_count = Common::trimSpace(htmlspecialchars($_POST['image_count']));
        try {
            // INSERT INTO ---
            $sql = 'INSERT INTO products (product_name, category_id, product_explanation) 
                    VALUES (:product_name, :category_id, :product_explanation)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':product_name', $name);
            $stmt->bindParam(':category_id', $id);
            $stmt->bindParam(':product_explanation', $explanation);
            $stmt->bindParam(':image_count', $image_count);
            $result = $stmt->execute();
        } catch (PDOException $e) {
            error_log($e);
            return false;
        }
        $this->db = null;
        return $result;
    }

    // 在庫あり商品情報を取得
    public function GetByStocks($p, $item, $catid)
    {
        $page = 0;
        // var_dump($p);
        if($p > 1){
            $page = 9*($p-1);
        }elseif($p == 1){
            $page = 0;
        }else{
            $page = 0;
        }
        // var_dump($page);
        $searchword = "";
        if($item != NULL){
            $searchword = '%' . $item . '%';
        }
        $item_limit = (int)$this->items_limit;
        // var_dump($searchword);
        try{
            $sql1 = ' SELECT products_sku.product_id AS product_id, min(products_sku.price) AS price, products.product_name AS product_name
                from products_sku 
                join products on products_sku.product_id = products.product_id
                where products_sku.stock_quantity > 0 ';
            $sql2 = ' group by product_id ORDER BY products.product_id DESC LIMIT :item_limit OFFSET :page ';   
            if($catid != 0){
                $sql1 .= ' AND products.category_id = :catid';
            }
            if($searchword != NULL){
                $sql1 .= ' AND products.product_name LIKE :product_name';
            }
            $sql = $sql1.$sql2;
            // var_dump($sql);
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':page',$page,\PDO::PARAM_INT);
            $stmt->bindParam(':item_limit',$item_limit,\PDO::PARAM_INT);
            if($catid != 0){
                $stmt->bindParam(':catid', $catid,\PDO::PARAM_INT);
            }
            if($searchword != ""){
                $stmt->bindParam(':product_name', $searchword,\PDO::PARAM_STR);
            }
            $stmt->execute();
            $result = $stmt->fetchAll();
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }
        return $result;
    }
    
    // 在庫ありの商品ID総数を取得する
    public function GetQuantityByStocks($item, $catid) {
        $searchword = NULL;
        if($item != NULL){
            $searchword = '%' . $item . '%';
        }
        try {
            $sql1 = 'SELECT COUNT(DISTINCT products.product_id ) as c
                        from products
                        join products_sku on products_sku.product_id = products.product_id
                        where products_sku.stock_quantity > 0';
            if($catid != 0){
                $sql1 .= ' AND products.category_id = :catid';
            }
            if($searchword != NULL){
                $sql1 .= ' AND products.product_name LIKE :product_name';
            }
            $sql = $sql1;
            $stmt = $this->db->prepare($sql);
            if($catid != 0){
                $stmt->bindParam(':catid', $catid,\PDO::PARAM_INT);
            }
            if($searchword != NULL){
                $stmt->bindParam(':product_name', $searchword,\PDO::PARAM_STR);
            }
            $stmt->execute();
            $results = $stmt->fetchAll();
            $result = $results[0] ["c"];
        } catch (PDOException $pe) {
            error_log($pe);
            return false;
        }
        // var_dump($result);
        return $result;
    }

    // 新商品一覧を取得する
    public function getNewProducts() {
        try {
            $sql = 'SELECT products_sku.product_id AS product_id, min(products_sku.price) AS price, products.product_name AS product_name
                    from products_sku 
                    join products on products_sku.product_id = products.product_id
                    where products_sku.stock_quantity > 0 
                    group by product_id ORDER BY products.product_id DESC LIMIT 6';
            $stmt = $this->db->query($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
        } catch (PDOException $pe) {
            error_log($pe);
            return false;
        }
        return $results;
    }

    // カテゴリ名を取得する
    public function GetNameByCategory($catid){
        try {
            $sql = ' SELECT category_name FROM categories WHERE category_id = :category_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':category_id',$catid);
            $stmt->execute();
            $results = $stmt->fetch();
            $result = $results["category_name"];
        }catch(\PDOException $e){
            error_log($e);
            return false;
        }
        return $result;
    }

}