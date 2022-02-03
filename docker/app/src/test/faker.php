<?php
// Faker autoloadeをrequireすることで使える
require_once(dirname(__FILE__, 2) . '/vendor/fzaninotto/faker/src/autoload.php');
// パラメーター
const DB_HOST = "db";
const DB_NAME = "sozo";
const DB_PORT = "3306";
const DB_USER = "root";
const DB_PASS = "root";

function create_pdo()
{
    $options = [
        // 接続した後に実行されるコマンド
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET 'utf8'",
        // エラー時の処理 -> 例外をスロー
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // データのフェッチした後のスタイル -> カラム名をキーとする連想配列
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    try {
        $pdo = new PDO(
            "mysql:dbname=".DB_NAME."; host=".DB_HOST."; port=".DB_PORT."; charset=utf8",
            DB_USER,
            DB_PASS,
            $options
        );
    } catch (Exception $e) {
        error_log($e);
    }
    return $pdo;
}



// フェイクデータを生成するジェネレータを作成
$faker = Faker\Factory::create('ja_JP');


$pdo = create_pdo();


// 商品データ情報の入力用
$arr = [
    [
        "name" => "ベージュの上着",
        "category" => 1,    // 1:アウター
        "explanation" => "ベージュの上着です。"
    ],
    [
        "name" => "グレーのシャツ",
        "category" => 2,    // 2:トップス
        "explanation" => "グレーのシャツです。"
    ],
    [
        "name" => "黒のスカート",
        "category" => 3,    // 3:ボトムス
        "explanation" => "黒のスカートです。"
    ],
    [
        "name" => "赤のコート",
        "category" => 1,    
        "explanation" => "鮮烈な赤のコートです。"
    ],
    [
        "name" => "紺のガウン",
        "category" => 1,
        "explanation" => "紺色に白のラインが映えるガウンです。"
    ],
    [
        "name" => "青と茶の上着",
        "category" => 1,
        "explanation" => "青と茶の上着です。"
    ],
    [
        "name" => "黒色シャツ",
        "category" => 2,    // 2:トップス
        "explanation" => "ジュエルネックの黒色シャツです。"
    ],
    [
        "name" => "赤色チェックのシャツ",
        "category" => 2,    // 2:トップス
        "explanation" => "赤色チレックのシャツです。"
    ],
    [
        "name" => "緑の上着",
        "category" => 2,    // 2:トップス
        "explanation" => "もこもこしていて羊みたいな緑色の上着。"
    ],
    [
        "name" => "黄色のコート",
        "category" => 1,    
        "explanation" => "黄色地に黒のスクエアが主張するコートです。"
    ],
    [
        "name" => "秋のコート",
        "category" => 1,    
        "explanation" => "秋を表現したコートです。"
    ],
    [
        "name" => "銀の靴",
        "category" => 6,    //6:シューズ    
        "explanation" => "銀色の靴です。"
    ],
    [
        "name" => "黒白縦じまパンツ",
        "category" => 3,
        "explanation" => "黒地の白縦じまのパンツです。"
    ],
    [
        "name" => "革のコート",
        "category" => 1,    
        "explanation" => "革のコートです。"
    ],
    [
        "name" => "青白縦じまパンツ",
        "category" => 3,
        "explanation" => "青色水色縦じまパンツです。"
    ],
    [
        "name" => "花柄靴",
        "category" => 6,
        "explanation" => "青地花柄の靴です。"
    ],
    [
        "name" => "赤色帽子",
        "category" => 4,    //4:アクセサリー
        "explanation" => "赤色のつば広帽子です。"
    ],
    [
        "name" => "黒色眼鏡",
        "category" => 4,
        "explanation" => "黒色丸眼鏡です。"
    ],
    [
        "name" => "革ベルト時計",
        "category" => 4,
        "explanation" => "革ベルトの時計です。"
    ],
    [
        "name" => "カラフルなセーター",
        "category" => 2,    
        "explanation" => "カラフルな模様のセーターです。"
    ],
    [
        "name" => "黒色ワンピース",
        "category" => 2,    
        "explanation" => "黒色シックなワンピースです。"
    ],
    [
        "name" => "白色フーディー",
        "category" => 2,    
        "explanation" => "白色のフーディーです。"
    ],
    [
        "name" => "薄水色のシャツ",
        "category" => 2,    
        "explanation" => "ジーン生地のシャツです。"
    ],
    [
        "name" => "黒色白格子のシャツ",
        "category" => 2,    
        "explanation" => "黒地に白格子のシャツです。"
    ]
];

for($i = 0; $i < 24; $i++){
    // products のダミーデータ
    try {
        $query = "INSERT INTO products (product_name, category_id, product_explanation) VALUES (:product_name, :category_id, :product_explanation)";
        $stmt = $pdo->prepare($query);

        $stmt->bindValue(":product_name", $arr[$i]["name"]);
        $stmt->bindValue(":category_id", $arr[$i]["category"]);
        $stmt->bindValue(":product_explanation", $arr[$i]["explanation"]);

        $stmt->execute();
    } catch (Exception $e) {
        error_log($e);
        echo "失敗2";
    }


    // products_sku のダミーデータ
    // とりあえずskuに三件入れます
    for($j = 0; $j < 3; $j++){
        try {
            $query = "INSERT INTO products_sku (product_id, size_id, condition_id, price, stock_quantity) VALUES (:product_id, :size_id, :condition_id, :price, :stock_quantity)";
            $stmt = $pdo->prepare($query);
            // product_id は 上で入れたproducts のidに合わせる
            $stmt->bindValue(":product_id", $i + 1);
            $stmt->bindValue(":size_id", mt_rand(1, 7));
            $stmt->bindValue(":condition_id", mt_rand(1, 5));
            $stmt->bindValue(":price", mt_rand(10, 50) * 100);
            $stmt->bindValue(":stock_quantity", 10);
            $stmt->execute();
        } catch (Exception $e) {
            error_log($e);
            echo "失敗3";
        }
    }

}



for ($i = 1; $i <= 10; $i++) {
    // customers のダミー

    $rand = mt_rand(0, 1);

    // 1 ならdeleteされていないもの、 0 ならdelete されたもの
    if($rand == 1){
        try {
            $password = password_hash("pass", PASSWORD_DEFAULT);
    
            $query = "INSERT INTO customers (email_address, password, nickname, created_at) VALUES (:email_address, :password, :nickname, CURRENT_TIMESTAMP)";
            $stmt = $pdo->prepare($query);
    
            $stmt->bindValue(":email_address", $faker->safeEmail);
            $stmt->bindValue(":password", $password);
            $stmt->bindValue(":nickname", $faker->firstKanaName);
            // $stmt->bindValue(":delivery_id", $i * 3 - 2); // 1,4,7,...
            
            $stmt->execute();
        } catch (Exception $e) {
            error_log($e);
            echo "失敗1";
        }
    }else{
        try {
            $password = password_hash("pass", PASSWORD_DEFAULT);
    
            $query = "INSERT INTO customers (email_address, password, nickname, created_at, withdrawed_at) VALUES (:email_address, :password, :nickname, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $stmt = $pdo->prepare($query);
    
            $stmt->bindValue(":email_address", $faker->safeEmail);
            $stmt->bindValue(":password", $password);
            $stmt->bindValue(":nickname", $faker->firstKanaName);
            // $stmt->bindValue(":delivery_id", $i);
            
            $stmt->execute();
        } catch (Exception $e) {
            error_log($e);
            echo "失敗1";
        }
    }

    // 入力したcustomer_id を取得
    $query = "select customer_id from customers order by customer_id desc limit 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $cusID = $stmt->fetchColumn();
    // var_dump($cusID);
    

    // deliveries のダミー、顧客ごとに三件ずつ
    for($j = 0; $j < 3; $j++){
        try {
            $tel = $faker->phoneNumber;
            $telnum = str_replace('-', '', $tel);

            $rand = mt_rand(0, 1);
            // 1 なら建物名あり、 0 ならなし
            if($rand == 1){
                $building = $faker->secondaryAddress();
            }else{
                $building = "''";
            }

            // 住所生成
            $city = $faker->city() . $faker->streetName();
            $street = explode("町", $faker->streetAddress());
            $address = $city . "," . $street[1] . "," . $building;

            $query = "INSERT INTO deliveries (customer_id, name, name_kana, telephone_number, post_number, area_id, address) VALUES (:customer_id, :name, :name_kana, :telephone_number, :post_number, :area_id, :address)";
            $stmt = $pdo->prepare($query);

            $stmt->bindValue(':customer_id', $cusID);
            $stmt->bindValue(':name', $faker->name);
            $stmt->bindValue(':name_kana', $faker->kanaName);
            $stmt->bindValue(':telephone_number', $telnum);
            $stmt->bindValue(':post_number', $faker->postcode);
            $stmt->bindValue(':area_id', mt_rand(1, 47));
            $stmt->bindValue(':address', $address);
        
            $stmt->execute();
        } catch (Exception $e) {
            error_log($e);
            echo "失敗3";
        }
    }

    // customers のdelivery_id を更新
    $query = "select delivery_id from deliveries where customer_id = :customer_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':customer_id', $cusID);
    $stmt->execute();

    $delID = $stmt->fetchAll();

    $query = "update customers set delivery_id = :id where customer_id = :customer_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':customer_id', $cusID);
    $stmt->bindValue(':id', $delID[mt_rand(0, 2)]["delivery_id"]);
    
    $stmt->execute();

}

for($i = 1; $i <= 10; $i++){ // 10: orders に入れる注文数

    // orders に入れるためのdeliveries 情報を取得
    $name_query = 'select name, name_kana, telephone_number, post_number, area_id, address from deliveries where customer_id = :customer_id';
    $stmt = $pdo->prepare($name_query);
    $stmt->bindValue(':customer_id', 1);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $delivery = $result[mt_rand(0, 2)]; // 三件のうちのどれか
    
    // orders に会計情報以外を入力
    $query = 'insert into orders (customer_id, name, name_kana, telephone_number, post_number, area_id, address, status_id) values 
    (:customer_id, :name, :name_kana, :telephone_number, :post_number, :area_id, :address, :status_id)';
    $stmt = $pdo->prepare($query);

    $stmt->bindValue(':customer_id', 1);


    $stmt->bindValue(':name', $delivery['name']);
    $stmt->bindValue(':name_kana', $delivery['name_kana']);
    $stmt->bindValue(':telephone_number', $delivery['telephone_number']);
    $stmt->bindValue(':post_number', $delivery['post_number']);
    $stmt->bindValue(':area_id', $delivery['area_id']);
    $stmt->bindValue(':address', $delivery['address']);
    $stmt->bindValue(':status_id', mt_rand(1, 5));


    $stmt->execute();

    // 重複無しランダム数列作成
    $arr = [];
    $min = 1;
    $max = 72;  // 商品24種 * 3 で、 sku は 72 種類
    for ($j = 0; $j < 3; $j++) {
        while (true) {
            // 一時的な乱数を作成
            $tmp = mt_rand($min, $max);

            // 乱数配列に含まれているならwhile続行、
            // 含まれてないなら配列に代入してbreak
            if (! in_array($tmp, $arr)) {
                array_push($arr, $tmp);
                break;
            }
        }
    }

    // order_histories_id を取得
    $query = "select order_histories_id from orders order by order_histories_id desc limit 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $history_id = $stmt->fetchColumn();

    // order_details へとりあえず3件入力
    for ($j = 0; $j < 3; $j++) {
        $query = 'insert into order_details 
            (order_histories_id, sku_id, order_quantity) 
            values 
            (:order_histories_id, :sku_id, :order_quantity)';
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':order_histories_id', $history_id);
        $stmt->bindValue(':sku_id', $arr[$j]);
        $stmt->bindValue(':order_quantity', mt_rand(1, 3));
        $stmt->execute();
    }


    $subtotal = 0;

    // 値段などの取得
    $query = 'select details.order_histories_id, details.sku_id, details.order_quantity, sku.price 
    from order_details as details join products_sku as sku on details.sku_id = sku.sku_id
    where order_histories_id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':id', $history_id);
    $stmt->execute();
    $price = $stmt->fetchAll();

    for($j = 0; $j < 3; $j++){  // 3: order_histories  に入ってる分
        $subtotal += $price[$j]["order_quantity"] * $price[$j]["price"];
    }
    $tax = (int)$subtotal * 0.1;
    $amount = $subtotal + $tax + 1000;

    // orders をupdate
    $query = "update orders set subtotal = :subtotal, consumption_tax = :consumption_tax, charge = :charge, amount = :amount where order_histories_id = :id";
    $stmt = $pdo->prepare($query);

    $stmt->bindValue(':subtotal', $subtotal);
    $stmt->bindValue(':consumption_tax', $tax);
    $stmt->bindValue(':charge', 1000);
    $stmt->bindValue(':amount', $amount);
    $stmt->bindValue(':id', $history_id);
    $stmt->execute();

}

// carts への入力
for($i = 0; $i < 10; $i++){

    // 重複無しランダム数列作成
    $arr_sku = [];
    $min = 1;
    $max = 72;
    for ($j = 0; $j < 3; $j++) {
        while (true) {
            // 一時的な乱数を作成
            $tmp = mt_rand($min, $max);

            // 乱数配列に含まれているならwhile続行、
            // 含まれてないなら配列に代入してbreak
            if (! in_array($tmp, $arr_sku)) {
                array_push($arr_sku, $tmp);
                break;
            }
        }
    }
    for($j = 0; $j < 3; $j++){
        $rand = mt_rand(0, 1);

        // 1 ならdeleteされていないもの、 0 ならdelete されたもの
        if($rand == 1){
            $query = 'insert into carts 
                (customer_id, sku_id, quantity) 
                values 
                (:customer_id, :sku_id, :quantity)';
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':customer_id', $i + 1);
            $stmt->bindValue(':sku_id', $arr_sku[$j]);
            $stmt->bindValue(':quantity', mt_rand(1, 3));
            $stmt->execute();
    
        }else{
            $query = 'insert into carts 
                (customer_id, sku_id, quantity, deleted_at) 
                values 
                (:customer_id, :sku_id, :quantity, CURRENT_TIMESTAMP)';
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':customer_id', $i + 1);
            $stmt->bindValue(':sku_id', $arr_sku[$j]);
            $stmt->bindValue(':quantity', mt_rand(1, 3));
            $stmt->execute();
    
        }
    }
}
