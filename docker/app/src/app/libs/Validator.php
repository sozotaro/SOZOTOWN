<?php

namespace App\libs;

require(__DIR__ . '/../../vendor/autoload.php');

use Valitron;

/**
 *　Valitronによるvalidationを担当するClass
 */
class Validator
{

    /**
     * @var array
     */
    private $post;

    public function __construct() {

    }

    //validation処理
    public static function validate(array $post) :? array {
        // インスタンスの生成
        Valitron\Validator::lang('ja');
        $v = new Valitron\Validator($post);

        // ラベルを日本語表記に変換する
        $v->labels([
            'name' => '名前',
            'name_kana' => 'フリガナ',
            'email_address' => 'メールアドレス',
            'email_address_confirm' => '確認用メールアドレス',
            'password' => 'パスワード',
            'password_confirm' => '確認用パスワード',
            'nickname' => 'ニックネーム',
            'post_number' => '郵便番号',
            'pref' => '都道府県',
            'city' => '市区町村',
            'street' => '番地',
            'building' => '建物名',
            'telephone_number' => '電話番号',
            'search' => '検索ワード',
            'new_password' => '新しいパスワード',
            'new_password_confirm' => '新しいパスワード(確認)'
        ]);

        // カスタムルールの作成

        // 住所のvalidationルール
        Valitron\Validator::addRule('address', function ($field, $value){
            $japanese = preg_match("/^[ぁ-んァ-ヶ一-龥々０-９ａ-ｚＡ-Ｚー・a-zA-Z0-9\-]+$/u", $value);

            if(!empty($japanese)){
                return true;
            }else{
                return false;
            }
        }, '住所の形式で入力してください');

        // validationのルールを設定する
        $v->rule('required', Validator::formItems())
            ->message('{field}は必須項目です');
        $v->rule('email',['email_address','email_address_confirm'])
            ->message('メールアドレス形式ではありません');
        if(!empty($post['email_address_confirm'])){
            $v->rule('equals', 'email_address','email_address_confirm')
            ->message('{field}が一致しません');
        }
        $v->rule('slug',['password','password_confirm'])
            ->message('{field}は半角英数字と-と_しか使えません');
        $v->rule('lengthMin', ['password','password_confirm'], 8)
            ->message('{field}は8文字以上必要です');
        if(!empty($post['password_confirm'])){
            $v->rule('equals', 'password', 'password_confirm')
            ->message('{field}が一致しません');
        }
        $v->rule('slug','nickname')
            ->message('{field}は半角英数字と-と_しか使えません');
        $v->rule('lengthMax','nickname', 12)
            ->message('{field}は12文字以内で入力してください');
        $v->rule('regex','name', '/^[ぁ-んァ-ヶー一-龠]+$/u')
            ->message('{field}は全角で入力してください');
        $v->rule('regex', 'name_kana', '/^[ア-ン゛゜ァ-ォャ-ョー「」、]+$/u')
            ->message('{field}はカタカナで入力してください。');
        $v->rule('address', ['city', 'street', 'building'])
            ->message('{field}には住所の形式で入力してください');
        $v->rule('numeric',['pref', 'post_number', 'telephone_number'])
            ->message('{field}には数字を入力してください');
        $v->rule('regex', 'telephone_number','/\A0\d{1,4}[-]?\d{1,4}[-]?\d{3,4}\z/')
            ->message('電話番号の形式が正しくありません');
            // [-(][-)]→[-]に変更

        // 追加
        
        $v->rule('lengthMax','post_number', 8)
            ->message('{field}は8文字以内で入力してください');
        $v->rule('lengthMax','name_kana', 40)
            ->message('{field}は40文字以内で入力してください');
        $v->rule('required', ['name','name_kana','post_number','pref','city','street','telephone_number','new_password','new_password_confirm'])
            ->message('{field}は必須項目です');
        $v->rule('slug',['new_password','new_password_confirm'])
            ->message('{field}は半角英数字と-と_しか使えません');
        $v->rule('lengthMin', ['new_password','new_password_confirm'], 8)
            ->message('{field}は8文字以上必要です');
        if(!empty($post['new_password_confirm'])){
            $v->rule('equals', 'new_password', 'new_password_confirm')
            ->message('{field}が一致しません');
        }
    
        // validationの実行
        if ($v->validate()) {
            $err_msgs = [];
        } else {
            $err_msgs = $v->errors();
        }
        return $err_msgs;
    }

        // フォームの必須項目
        protected static function formItems() : array{
            return [
                'email_address',
                'email_address_confirm',
                'password',
                'password_confirm',
                'nickname'
            ];
        }
}