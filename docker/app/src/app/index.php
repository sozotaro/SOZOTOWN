<?php

// デバッグ中のignore notice
// error_reporting(E_ALL & ~E_NOTICE);
require __DIR__ . '/../vendor/autoload.php';

use App\libs\Common;
use App\Route\Route;

// use function FastRoute\simpleDispatcher;

/*
 * 　ルーティング定義部分はApp\\Routeに移動しました)
**/
@session_start();
Route::route();

//if ($_SERVER["REQUEST_METHOD"] === "POST") {
//    session_start();
//    Common::checkCsrfKey();
//    var_dump($_SESSION['key']);
//    if (isset($_SESSION["key"], $_POST["key"]) && $_SESSION["key"] == $_POST["key"]) {
//        unset($_SESSION["key"]);
// post_message();
//    }