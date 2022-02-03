USE sozo;

SET CHARACTER_SET_CLIENT = utf8;
SET CHARACTER_SET_CONNECTION = utf8;

-- 複数SQL定義のために、SQLの区切り文字を ; から // に変更
DELIMITER //

-- Stock.php
-- 全在庫情報の取得
drop procedure if exists getAllStocksProc//
create procedure getAllStocksProc()
begin
	select * from stocks;
end
//

-- 新しい在庫を登録する
drop procedure if exists createStockProc//
create procedure createStockProc(IN in_product_name varchar(50), IN in_size_id int(2), IN in_condition_id int(8), IN in_price int(10), IN in_stock_quantity int(8))
begin
	insert into stocks (product_id, size_id, condition_id, price, stock_quantity) values (in_product_name, in_size_id, in_condition_id, in_price, in_stock_quantity);
end
//


delimiter ;