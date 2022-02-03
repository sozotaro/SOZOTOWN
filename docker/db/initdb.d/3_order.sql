USE sozo;

SET CHARACTER_SET_CLIENT = utf8;
SET CHARACTER_SET_CONNECTION = utf8;

-- 複数SQL定義のために、SQLの区切り文字を ; から // に変更
DELIMITER //

-- 全受注履歴の取得
drop procedure if exists getAllOrders//
create procedure getAllOrders()
begin
	select * from orders;
end
//

-- -- 新規データ作成
-- drop procedure if exists updateCustomerProc//
-- create procedure create(IN in_customer_id int(8), IN in_subtotal int(12), IN in_consumption_tax int(8), IN in_charge int(4), IN in_delivery_id int(8), IN in_status_id int(8), IN in_ordered_at datetime)
-- begin
-- 	insert into orders (customer_id, subtotal, consumption_tax, charge, delivery_id, status_id , ordered_at) values (in_customer_id, in_subtotal, in_consumption_tax, in_charge, in_delivery_id, in_status_id, in_ordered_at);
-- end
-- //




-- 以下松田

-- 注文情報を登録する
-- 1.受注履歴テーブルに登録する
drop procedure if exists orderRegistrationProc//
create procedure orderRegistrationProc(
    IN in_customer_id int(8), 
    IN in_subtotal int(12), 
    IN in_consumption_tax int(8), 
    IN in_charge int(4), 
    IN in_status_id int(8), --
    in in_name varchar(20), 
    in in_name_kana varchar(40), 
    in in_telephone_number int(14), 
    in in_post_number int(8), 
    in in_area_id int(2), 
    in in_address varchar(120)
)
begin
	INSERT INTO orders
        (customer_id, subtotal, consumption_tax, charge, status_id, name, name_kana, telephone_number,
                post_number, area_id, address)
        VALUES
        (in_customer_id, in_subtotal, in_consumption_tax, in_charge, in_status_id, in_name, in_name_kana, in_telephone_number,
                in_post_number, in_area_id, in_address);
end
//

-- 受注詳細テーブルに登録する
drop procedure if exists orderRegistrationProc2//
create procedure orderRegistrationProc2(in in_order_histories_id int(8), in in_sku_id int(8), in in_order_quantity int(8))
begin
    INSERT INTO order_details 
        (order_histories_id, sku_id, order_quantity) 
        VALUES
        (in_order_histories_id, in_sku_id, in_order_quantity);
end
//

-- SKUの在庫を変更する
drop procedure if exists orderRegistrationProc3//
create procedure orderRegistrationProc3(in in_sku_id int(8), in in_stock_quantity int(8))
begin
    UPDATE products_sku SET stock_quantity = stock_quantity - in_stock_quantity WHERE sku_id = in_sku_id;
end
//



-- 注文履歴の一覧を取得する
-- int(8)は適当
drop procedure if exists getOrderListProc//
create procedure getOrderListProc(in in_customer_id int(8), in in_page int(8), in in_order_limit int(8))
begin
    SELECT 
        orders.order_histories_id,
        FORMAT(orders.subtotal + orders.charge ,0) as subtotal,
        (
            SELECT COUNT(*) 
                FROM 
                    order_details 
                WHERE 
                    order_details.order_histories_id = orders.order_histories_id  
        ) as count,
        p.product_name,
        DATE_FORMAT(orders.ordered_at,'%Y/%m/%d') as ordered_at,
        s.status_name as status
    FROM
        orders
        LEFT JOIN statuses s on orders.status_id = s.status_id
        LEFT JOIN order_details od on orders.order_histories_id = od.order_histories_id
        LEFT JOIN products_sku ps on od.sku_id = ps.sku_id
        LEFT JOIN products p on ps.product_id = p.product_id
    WHERE customer_id = in_customer_id
    ORDER BY order_histories_id DESC LIMIT in_order_limit OFFSET in_page;
end
//

-- 注文履歴の詳細を表示する
drop procedure if exists getOrderDetailProc//
create procedure getOrderDetailProc(in in_order_histories_id int(8))
begin
    SELECT
        p.product_id,
        p.product_name,
        FORMAT(ps.price,0) as subtotal,
        p.product_explanation,
        s.size_name,
        c.condition_name
    FROM order_details
        LEFT JOIN products_sku ps on order_details.sku_id = ps.sku_id
        LEFT JOIN products p on ps.product_id = p.product_id
        LEFT JOIN sizes s on ps.size_id = s.size_id
        LEFT JOIN conditions c on ps.condition_id = c.condition_id
    WHERE order_details.order_histories_id = in_order_histories_id;
end
//

-- 都道府県名をidから取得する
drop procedure if exists getPrefByIdProc//
create procedure getPrefByIdProc(in in_area_id int(2))
begin
    SELECT area_name FROM areas WHERE area_id = in_area_id;
end
//

-- 購入履歴数を取得する
drop procedure if exists GetQuantityByOrderHistoriesProc//
create procedure GetQuantityByOrderHistoriesProc(in in_customer_id int(8))
begin
    SELECT COUNT(order_histories_id) AS c
                    from orders
                    where customer_id = in_customer_id;
end
//

delimiter ;