<?php

namespace App\Model;

use Dotenv\Dotenv;
use PDO;
use PDOException;

require(__DIR__ . '/../../vendor/autoload.php');
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// パラメーター
define("DB_HOST", $_ENV['DB_HOST']);
define("DB_PORT", $_ENV['DB_PORT']);
define("DB_NAME", $_ENV['DB_NAME']);
define("DB_USER", $_ENV['DB_USER']);
define("DB_CHARSET", $_ENV['DB_CHARSET']);
define("DB_PASS", $_ENV['DB_PASSWORD']);

class DB extends PDO
{
    private $dsn = 'mysql:dbname=' . DB_NAME . '; host=' . DB_HOST . '; port=' . DB_PORT . '; charset=' . DB_CHARSET;
    // TODO:DBのユーザーが増えるなら、ここではメンバ変数の宣言だけしてコンストラクタへ直接渡せるようにする予定
    private $username = DB_USER;
    private $password = DB_PASS;

    private $options = [
        // 接続した後に実行されるコマンド
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET 'utf8'",
        // エラー時の処理 -> 例外をスロー
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // データのフェッチした後のスタイル -> カラム名をキーとする連想配列
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    public function __construct()
    {
        $dsn = $this->dsn;
        $username = $this->username;
        $password = $this->password;
        $options = $this->options;
        parent::__construct($dsn, $username, $password, $options);
        try {
            $pdo = new PDO($this->dsn,$this->username,$this->password,$this->options);
        }catch (PDOException $pe){
            error_log($pe);
            return false;
        }
        return $pdo;
    }
}

