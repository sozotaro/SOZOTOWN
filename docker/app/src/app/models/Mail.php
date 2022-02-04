<?php

namespace App\Model;

// PHPMailer クラスをネーム空間にインポート
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require (__DIR__.'/../../vendor/autoload.php');

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

define("SERVER_URI", $_ENV['SERVER_URI']);
// var_dump($_ENV);

// メール日本語対応
mb_language("japanese");
mb_internal_encoding("UTF-8");

class Mail
{
    private $mail;

    //DB接続
    public function __construct()
    {
        $this->mail = new PHPMailer(true);
    }
    // 仮会員登録メール送信
    public function tempoRegisSendMail(string $to, string $nickname, string $message){
        // インスタンス生成
        $this->mail;
        
        $subject = '仮登録確認';

        try {
            // SMTPの設定
            $this->mail->isSMTP();                       // SMTP 利用
            $this->mail->Host       = 'smtp.gmail.com';  // SMTP サーバー(Gmail の場合これ)
            $this->mail->SMTPAuth   = true;              // SMTP認証を有効にする
            $this->mail->Username   = 'sumple@gmail.com'; // ユーザ名 (Gmail ならメールアドレス)
            $this->mail->Password   = 'sumple';      // パスワード
            $this->mail->SMTPSecure = 'tls';             // 暗号化通信 (Gmail では使えます)
            $this->mail->Port       = 587;               // TCP ポート (TLS の場合 587)

            // メール本体
            $this->mail->setFrom('sumple@gmail.com', 'SOZOTOWN');  // 送信元メールアドレスと名前
            $this->mail->addAddress($to, mb_encode_mimeheader($nickname, 'ISO-2022-JP'));  // 送信先メールアドレスと名前
            $this->mail->Subject = mb_encode_mimeheader($subject, 'ISO-2022-JP');  // 件名
            $this->mail->Body    = mb_convert_encoding($message, "JIS","UTF-8");  // 本文

        // 送信
        $this->mail->send();
        } catch (Exception $e) {
            echo "送信失敗: {$this->mail->ErrorInfo}";
        }
    }

    // 退会完了メール送信
    public function withdrawedSendMail($to, $nickname){
        // インスタンス生成
        $this->mail;
        
        $subject = '退会完了';
        $message = '退会完了しました。';

        try {
            // SMTPの設定
            $this->mail->isSMTP();                       // SMTP 利用
            $this->mail->Host       = 'smtp.gmail.com';  // SMTP サーバー(Gmail の場合これ)
            $this->mail->SMTPAuth   = true;              // SMTP認証を有効にする
            $this->mail->Username   = 'sumple@gmail.com'; // ユーザ名 (Gmail ならメールアドレス)
            $this->mail->Password   = 'sumple';      // パスワード
            $this->mail->SMTPSecure = 'tls';             // 暗号化通信 (Gmail では使えます)
            $this->mail->Port       = 587;               // TCP ポート (TLS の場合 587)

            // メール本体
            $this->mail->setFrom('sumple@gmail.com', 'SOZOTOWN');  // 送信元メールアドレスと名前
            $this->mail->addAddress($to, mb_encode_mimeheader($nickname, 'ISO-2022-JP'));  // 送信先メールアドレスと名前
            $this->mail->Subject = mb_encode_mimeheader($subject, 'ISO-2022-JP');  // 件名
            $this->mail->Body    = mb_convert_encoding($message, "JIS","UTF-8");  // 本文

            // 送信
            $this->mail->send();
        } catch (Exception $e) {
            echo "送信失敗: {$this->mail->ErrorInfo}";
            exit();
        }
    }

    // パスワードリセットメール送信
    public function passwordResetSendMail($to){
        // URL作成
        $url = SERVER_URI . "newpass/?email=" . $to;
        $message = "下記のURLをクリックしてパスワードを変更してください。\r\n" . $url;
        // インスタンス生成
        $this->mail;
        
        $subject = 'パスワードリセット';

        try {
            // SMTPの設定
            $this->mail->isSMTP();                       // SMTP 利用
            $this->mail->Host       = 'smtp.gmail.com';  // SMTP サーバー(Gmail の場合これ)
            $this->mail->SMTPAuth   = true;              // SMTP認証を有効にする
            $this->mail->Username   = 'sumple@gmail.com'; // ユーザ名 (Gmail ならメールアドレス)
            $this->mail->Password   = 'sumple';      // パスワード
            $this->mail->SMTPSecure = 'tls';             // 暗号化通信 (Gmail では使えます)
            $this->mail->Port       = 587;               // TCP ポート (TLS の場合 587)

            // メール本体
            $this->mail->setFrom('sumple@gmail.com', 'SOZOTOWN');  // 送信元メールアドレスと名前
            $this->mail->addAddress($to, mb_encode_mimeheader($to, 'ISO-2022-JP'));  // 送信先メールアドレスと名前
            $this->mail->Subject = mb_encode_mimeheader($subject, 'ISO-2022-JP');  // 件名
            $this->mail->Body    = mb_convert_encoding($message, "JIS","UTF-8");  // 本文

            // 送信
            $this->mail->send();
        } catch (Exception $e) {
            echo "送信失敗: {$this->mail->ErrorInfo}";
            exit();
        }
    }

    // 注文確定メール送信
    public function OrderSendMail($to, $carts){
        // 購入商品情報
        $cnt = count($carts);
        // var_dump($carts);
        $total = 0;
        // URL作成
        $message = "ご注文ありがとうございました。\r\n［ご注文内容］";
        for ($i = 0;$i < $cnt;$i++){
            $product_name = $carts[$i]['product_name'];
            $price = (int)$carts[$i]['price'];
            $priceNumber =  number_format($price, 0);
            $quantity = (int)$carts[$i]['quantity'];
            $subtotal = $price*$quantity;
            $subtotalNumber =  number_format($subtotal, 0);
            $total += $subtotal;
            $message .= "\r\n商品名：".$product_name."\r\n金額：".$priceNumber."円"."\r\n個数：".$quantity."\r\n計：".$subtotalNumber."円\r\n";
            // var_dump($message);
        }
        $tax = floor($total*0.1);
        $totalTax =  number_format(($total+$tax+1000), 0);
        $taxNumber = number_format($tax, 0);
        $total = number_format($total, 0);
        $message .= "\r\n小計：".$total."円\r\n消費税：".$taxNumber."円\r\n送料：1,000円\r\n合計金額(消費税込み)：".$totalTax."円";
        // インスタンス生成
        $this->mail;
        
        $subject = 'ご注文';

        try {
            // SMTPの設定
            $this->mail->isSMTP();                       // SMTP 利用
            $this->mail->Host       = 'smtp.gmail.com';  // SMTP サーバー(Gmail の場合これ)
            $this->mail->SMTPAuth   = true;              // SMTP認証を有効にする
            $this->mail->Username   = 'sumple@gmail.com'; // ユーザ名 (Gmail ならメールアドレス)
            $this->mail->Password   = 'sumple';      // パスワード
            $this->mail->SMTPSecure = 'tls';             // 暗号化通信 (Gmail では使えます)
            $this->mail->Port       = 587;               // TCP ポート (TLS の場合 587)

            // メール本体
            $this->mail->setFrom('sumple@gmail.com', 'SOZOTOWN');  // 送信元メールアドレスと名前
            $this->mail->addAddress($to, mb_encode_mimeheader($to, 'ISO-2022-JP'));  // 送信先メールアドレスと名前
            $this->mail->Subject = mb_encode_mimeheader($subject, 'ISO-2022-JP');  // 件名
            $this->mail->Body    = mb_convert_encoding($message, "JIS","UTF-8");  // 本文

            // 送信
            $this->mail->send();
        } catch (Exception $e) {
            echo "送信失敗: {$this->mail->ErrorInfo}";
            exit();
        }
    }


}