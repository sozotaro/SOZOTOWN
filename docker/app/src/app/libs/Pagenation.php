<?php

namespace App\libs;

use App\Model\Product;
use Dotenv\Dotenv;

require(__DIR__ . '/../../vendor/autoload.php');
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// パラメーター
define("PAGINATE_ITEMS", $_ENV['PAGINATE_ITEMS']);
define("PAGINATE_ORDER", $_ENV['PAGINATE_ORDER']);

class Pagenation
{
    private $items_limit  = PAGINATE_ITEMS;
    private $order_items_limit  = PAGINATE_ORDER;

    // 商品一覧用pagenation
    public function pagenation_list($p, $number){
        @session_start();
        // 1ページの上限
        $items_limit = (int)$this->items_limit;
        // 最初のページ番号
        $min = 1;
        // 最後のページ番号
        $max = (int) ceil($number / $items_limit);
        if(isset($_SESSION['max'])){
            unset($_SESSION['max']);
        }
        $_SESSION['max'] = $max;
        $page = $this->pagenation_page($p, $min, $max);
        // var_dump($page);
        return $page;
    }

    // 注文履歴用pagenation
    public function order_pagenation_list($p, $number){
        @session_start();
        // 1ページの上限
        $items_limit = (int)$this->order_items_limit;
        // var_dump($items_limit);
        // 最初のページ番号
        $min = 1;
        // 最後のページ番号
        $max = (int) ceil($number / $items_limit);
        $_SESSION['max'] = $max;
        $page = $this->pagenation_page($p, $min, $max);
        // var_dump($page);
        return $page;
    }

    // pagenation範囲確認
    public function pagenation_page($p, $min, $max)
    {
        $page = array();
        // var_dump($p);
        // var_dump($min);
        // var_dump($max);
        // 範囲外か
        if (!($min <= $p && $p <= $max)) {
            $p = 1; //強制的に1ページ目へ
            return $p;
        }
        // 最初のページか ?
        if ($p === $min) {
            // 1ページのみか？
            if($p === $max){
                return [
                    "first" => NULL, 
                    "next" => NULL,
                    "status" => NULL,
                    "last" => NULL,
                    "final" => NULL,
                ];
            }else{
                for ($i = $max; $i >= 1; $i--){
                    array_unshift($page, $i);
                }
                return [
                    "first" => NULL, 
                    "next" => $min + 1,
                    "status" => $page,
                    "last" => NULL,
                    "final" => $max,
                ];
            }
        }
        // 最後のページか ?
        if ($p === $max) {
            for ($i = $p; $i >= 1; $i--){
                array_unshift($page, $i);
            }
            return [
                "first" => $min, 
                "next" => NULL,
                "status" => $page,
                "last" => $max - 1,
                "final" => NULL,
            ];
        }
        for ($i = $max; $i >= 1; $i--){
            array_unshift($page, $i);
        }
        return [
            "first" => $min, 
            "next" => $p + 1,
            "status" => $page,
            "last" => $p - 1,
            "final" => $max,
        ];
    }
}