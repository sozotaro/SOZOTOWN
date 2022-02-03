-- 管理者ユーザー
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'v&KV8+D9~hH~';
-- 全権限
GRANT ALL PRIVILEGES ON sozo . * TO 'admin'@'localhost';


-- 販売責任者ユーザー
CREATE USER 'manager'@'localhost' IDENTIFIED BY 'Psp/khWT7G#_';
-- 全権限テーブル
-- SKU
GRANT ALL PRIVILEGES ON sozo . products_sku TO 'manager'@'localhost';
-- 商品マスタ
GRANT ALL PRIVILEGES ON sozo . products TO 'manager'@'localhost';

-- 一部権限テーブル
-- 顧客マスタ
GRANT SELECT ON sozo . customers TO 'manager'@'localhost';
-- 配送先マスタ
GRANT SELECT ON sozo . deliveries TO 'manager'@'localhost';
-- 受注履歴マスタ
GRANT SELECT, UPDATE(status_id) ON sozo . orders TO 'manager'@'localhost';
-- 受注詳細マスタ
GRANT SELECT ON sozo . order_details TO 'manager'@'localhost';
-- エリアマスタ
GRANT SELECT ON sozo . areas TO 'manager'@'localhost';
-- ステータスマスタ
GRANT SELECT ON sozo . statuses TO 'manager'@'localhost';
-- 商品サイズマスタ
GRANT SELECT ON sozo . sizes TO 'manager'@'localhost';
-- コンディションマスタ
GRANT SELECT ON sozo . conditions TO 'manager'@'localhost';
-- 商品カテゴリマスタ
GRANT SELECT ON sozo . categories TO 'manager'@'localhost';
-- 権限マスタ
GRANT SELECT ON sozo . approves TO 'manager'@'localhost';
-- 権限ユーザーマスタ
GRANT SELECT, UPDATE(nickname, email_address, password) ON sozo . approve_users TO 'manager'@'localhost';


-- 顧客ユーザー
CREATE USER 'customer'@'localhost' IDENTIFIED BY 'Hj3CV-(pg72Y';

-- 全権限テーブル
-- カート
GRANT ALL PRIVILEGES ON sozo . carts TO 'customer'@'localhost';
-- 配送先マスタ
GRANT ALL PRIVILEGES ON sozo . deliveries TO 'customer'@'localhost';

-- 一部権限テーブル
-- 顧客マスタ
GRANT SELECT(email_address, password, nickname, delivery_id, password_token, reseted_at, password_locked_at, created_at, withdrawed_at), INSERT, UPDATE(email_address, password, nickname, delivery_id, password_token, reseted_at, password_locked_at, created_at, withdrawed_at) ON sozo . customers TO 'customer'@'localhost';
-- ログインエラー履歴
GRANT SELECT, INSERT ON sozo . login_errors TO 'customer'@'localhost';
-- 受注履歴
GRANT SELECT, INSERT ON sozo . orders TO 'customer'@'localhost';
-- 受注詳細
GRANT SELECT, INSERT ON sozo . order_details TO 'customer'@'localhost';
-- エリアマスタ
GRANT SELECT ON sozo . areas TO 'customer'@'localhost';
-- ステータスマスタ
GRANT SELECT ON sozo . statuses TO 'customer'@'localhost';
-- 商品サイズマスタ
GRANT SELECT ON sozo . sizes TO 'customer'@'localhost';
-- コンディションマスタ
GRANT SELECT ON sozo . conditions TO 'customer'@'localhost';
-- 商品カテゴリマスタ
GRANT SELECT ON sozo . categories TO 'customer'@'localhost';
-- 商品マスタ
GRANT SELECT ON sozo . products TO 'customer'@'localhost';
-- SKU
GRANT SELECT, UPDATE(stock_quantity) ON sozo . products_sku TO 'customer'@'localhost';

FLUSH PRIVILEGES;