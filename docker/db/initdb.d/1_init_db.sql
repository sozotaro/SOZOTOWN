SET CHARSET UTF8;
drop database if exists sozo;
CREATE DATABASE IF NOT EXISTS sozo DEFAULT CHARACTER SET utf8;

USE sozo;

SET CHARACTER_SET_CLIENT = utf8;
SET CHARACTER_SET_CONNECTION = utf8;

-- Project Name : sozo
-- Date/Time    : 2021/11/26 15:14:31
-- Author       : admin
-- RDBMS Type   : MySQL
-- Application  : A5:SQL Mk-2

/*
  << 注意！！ >>
  BackupToTempTable, RestoreFromTempTable疑似命令が付加されています。
  これにより、drop table, create table 後もデータが残ります。
  この機能は一時的に $$TableName のような一時テーブルを作成します。
  この機能は A5:SQL Mk-2でのみ有効であることに注意してください。
*/

-- ==========================================
-- 以下 create

-- ステータスマスタ
create table if not exists statuses (
  status_id INT(2) not null auto_increment
  , status_name VARCHAR(20) not null
  , constraint statuses_PKC primary key (status_id)
) ;

-- 権限マスタ
create table if not exists approves (
  approve_id INT(8) not null auto_increment
  , approve_name VARCHAR(20) not null
  , constraint approves_PKC primary key (approve_id)
) ;

-- 権限ユーザーマスタ
create table if not exists approve_users (
  approve_user_id INT(8) not null auto_increment
  , nickname VARCHAR(128)
  , email_address VARCHAR(256) not null
  , password VARCHAR(60) not null
  , approve_id INT(8) not null
  , constraint approve_users_PKC primary key (approve_user_id)
) ;

create index approve_users_IX1
  on approve_users(email_address);

-- 受注詳細
create table if not exists order_details (
  order_detail_id INT(8) not null auto_increment
  , order_histories_id INT(8) not null
  , sku_id INT(8)
  , order_quantity INT(8)
  , constraint order_details_PKC primary key (order_detail_id)
) ;

-- 受注履歴
create table if not exists orders (
  order_histories_id INT(8) not null auto_increment
  , customer_id INT(8) not null
  , subtotal INT(12)
  , consumption_tax INT(8)
  , charge INT(4)
  , amount INT(12)
  , name VARCHAR(20) not null
  , name_kana VARCHAR(40)
  , telephone_number VARCHAR(14)
  , post_number VARCHAR(8)
  , area_id INT(2)
  , address VARCHAR(120)
  , status_id INT(8) not null
  , ordered_at datetime not null default current_timestamp
  , constraint orders_PKC primary key (order_histories_id)
) ;

-- カート
create table if not exists carts (
  cart_id INT(8) not null auto_increment
  , customer_id INT(8) not null
  , sku_id INT(8) not null
  , quantity INT(8) not null
  , deleted_at DATETIME
  , constraint carts_PKC primary key (cart_id)
) ;

-- 仮登録
create table if not exists temporary_registrations (
  temporary_registration_id INT(8) not null auto_increment
  , customer_id INT(8) not null
  , access_token CHAR(8) not null
  , password_token VARCHAR(255) not null
  , temporary_registration_at datetime default current_timestamp
  , constraint temporary_registrations_PKC primary key (temporary_registration_id)
) ;

create index temporary_registrations_IX1
  on temporary_registrations(password_token);

-- ログインエラー履歴
create table if not exists login_errors (
  history_id INT(8) not null auto_increment
  , customer_id INT(8) not null
  , login_error_history_at DATETIME
  , constraint login_errors_PKC primary key (history_id)
) ;

-- エリアマスタ
create table if not exists areas (
  area_id INT(2) not null auto_increment
  , area_name CHAR(5) not null
  , constraint areas_PKC primary key (area_id)
) ;

create index areas_IX1
  on areas(area_name);

-- 配送先マスタ
create table if not exists deliveries (
  delivery_id INT(8) not null auto_increment
  , customer_id INT(8) not null
  , name VARCHAR(20) not null
  , name_kana VARCHAR(40)
  , telephone_number VARCHAR(14)
  , post_number VARCHAR(8)
  , area_id INT(2)
  , address VARCHAR(120)
  , constraint deliveries_PKC primary key (delivery_id)
) ;

create index deliveries_IX1
  on deliveries(area_id);

-- 顧客マスタ
create table if not exists customers (
  customer_id INT(8) not null auto_increment
  , email_address VARCHAR(256) not null
  , password VARCHAR(60) not null
  , nickname VARCHAR(128)
  , delivery_id INT(24)
  , password_token VARCHAR(60)
  , reseted_at DATETIME
  , password_locked_at DATETIME
  , created_at datetime
  , withdrawed_at DATETIME
  , constraint customers_PKC primary key (customer_id)
) ;

create index customers_IX1
  on customers(email_address,password_token);

-- コンディションマスタ
create table if not exists conditions (
  condition_id INT(8) not null auto_increment
  , condition_name VARCHAR(200) not null
  , constraint conditions_PKC primary key (condition_id)
) ;

-- 商品SKU
create table if not exists products_sku (
  sku_id INT(8) not null auto_increment
  , product_id INT(8) not null
  , size_id INT(2)
  , condition_id INT(8)
  , price INT(10)
  , stock_quantity INT(8) not null
  , constraint products_sku_PKC primary key (sku_id)
) ;

-- 商品サイズマスタ
create table if not exists sizes (
  size_id INT(2) not null auto_increment
  , size_name VARCHAR(10) not null
  , size_explanation VARCHAR(50)
  , constraint sizes_PKC primary key (size_id)
) ;

-- 商品カテゴリマスタ
create table if not exists categories (
  category_id INT(4) not null auto_increment
  , category_name VARCHAR(20)
  , constraint categories_PKC primary key (category_id)
) ;

-- 商品マスタ
create table if not exists products (
  product_id INT(8) not null auto_increment
  , product_name VARCHAR(50) not null
  , category_id INT(4)
  , product_explanation VARCHAR(300)
  , constraint products_PKC primary key (product_id)
) ;

-- ==========================================
-- 以下 insert

-- コンディションマスタ
INSERT 
INTO conditions(condition_name) 
VALUES ('S：未使用もしくは新品')
, ('A：使用感のほぼない、大変状態の良い')
, ('B：使用感はあるが、目立つダメージのない')
, ('C：部分的に目立つ使用感やダメージがある')
, ('D：全体的に使用感やダメージが目立つ');

-- ステータス
INSERT 
INTO statuses(status_name) 
VALUES ('受注完了')
, ('未発送')
, ('発送済')
, ('取引完了')
, ('キャンセル済');

-- サイズ
INSERT 
INTO sizes(size_name) 
VALUES ('XS')
, ('S')
, ('M')
, ('L')
, ('XL')
, ('XXL')
, ('フリーサイズ');

-- カテゴリー
INSERT INTO categories (category_name) VALUES
 ('アウター')
, ('トップス')
, ('ボトムス')
, ('アクセサリー')
, ('セットアップ')
, ('シューズ');


-- 権限
INSERT 
INTO approves(approve_name) 
VALUES ('管理者')
, ('販売責任者');


-- エリア
INSERT 
INTO areas (area_name)
VALUES ('北海道')
, ('青森県')
, ('岩手県')
, ('宮城県')
, ('秋田県')
, ('山形県')
, ('福島県')
, ('茨城県')
, ('栃木県')
, ('群馬県')
, ('埼玉県')
, ('千葉県')
, ('東京都')
, ('神奈川県')
, ('新潟県')
, ('富山県')
, ('石川県')
, ('福井県')
, ('山梨県')
, ('長野県')
, ('岐阜県')
, ('静岡県')
, ('愛知県')
, ('三重県')
, ('滋賀県')
, ('京都府')
, ('大阪府')
, ('兵庫県')
, ('奈良県')
, ('和歌山県')
, ('鳥取県')
, ('島根県')
, ('岡山県')
, ('広島県')
, ('山口県')
, ('徳島県')
, ('香川県')
, ('愛媛県')
, ('高知県')
, ('福岡県')
, ('佐賀県')
, ('長崎県')
, ('熊本県')
, ('大分県')
, ('宮崎県')
, ('鹿児島県')
, ('沖縄県');