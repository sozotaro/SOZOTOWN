USE sozo;

SET CHARACTER_SET_CLIENT = utf8;
SET CHARACTER_SET_CONNECTION = utf8;

-- 複数SQL定義のために、SQLの区切り文字を ; から // に変更
DELIMITER //


-- カート用プロシージャ

    -- カートの商品を表示する
    drop procedure if exists getCartDataProc//
    create procedure getCartDataProc(in in_customer_id int(8))
    begin
        SELECT
            carts.cart_id
                , carts.customer_id
                , carts.sku_id
                , products.product_name
                , products.product_explanation
                , products_sku.price
                , carts.quantity
                , sizes.size_name
                , conditions.condition_name
                , carts.deleted_at
        FROM carts
            LEFT JOIN products_sku
                        ON products_sku.sku_id = carts.sku_id
            LEFT JOIN products
                        ON products_sku.product_id = products.product_id
            LEFT JOIN sizes
                        ON products_sku.size_id = sizes.size_id
            LEFT JOIN conditions
                        ON products_sku.condition_id = conditions.condition_id
        WHERE
            carts.customer_id = in_customer_id
            AND
            carts.deleted_at IS NULL;
    end
    //


    -- カートに商品を追加する
    -- 在庫数量を確認するようにしています。問題あればご指摘お願いします
    -- stock_id を sku_idに修正してます
    drop procedure if exists addCartProc//
    create procedure addCartProc(IN in_customer_id int(8), IN in_sku_id int(8), IN in_quantity int(8))
    begin
        -- 返り値用のboolフラグ
        set @flag = true;
        -- SKUの在庫を取得
        select stock_quantity into @stock_quantity from products_sku where sku_id = in_sku_id;
        -- 在庫が注文数量より多ければ、カートに追加
        IF in_quantity <= @stock_quantity THEN
            insert into carts (customer_id, sku_id, quantity) values (in_customer_id, in_sku_id, in_quantity);
        -- 在庫以上の注文をされた場合 返り値をfalse
        ELSE
            set @flag = false;
        END IF;
        select @flag;
    end
    //

    -- カートの数量を変更する
    drop procedure if exists changeCartQuantityProc//
    create procedure changeCartQuantityProc(IN in_cart_id int(8), IN in_quantity int(8))
    begin
        -- 返り値用のboolフラグ
        set @flag = true;
        -- 指定cart_idのデータを取得
        select quantity into @quantity from carts where cart_id = in_cart_id;
        -- quantityが0より多ければ、正規データとして変更
        IF @quantity > 0 THEN
            UPDATE carts SET quantity = in_quantity WHERE cart_id = in_cart_id;
        -- 不正データの場合 返り値をfalse
        ELSE
            set @flag = false;
        END IF;
        select @flag;
    end
    //

    -- カートの商品を削除する
    -- 顧客のcustomer_id がDB情報と一致するか確認している。
    drop procedure if exists delCartItemProc//
    create procedure delCartItemProc(in in_customer_id int(8), IN in_cart_id int(8))
    begin
        -- 返り値用のboolフラグ
        set @flag = true;
        -- 指定cart_idのcustomer_id を取得
        select customer_id into @customer_id from carts where cart_id = in_cart_id;
        -- customer_id が ログインユーザーのIDと一致すれば、正規として変更
        IF @customer_id = in_customer_id THEN
            UPDATE carts SET deleted_at = CURRENT_TIMESTAMP WHERE cart_id = in_cart_id;
        -- 不正データの場合 返り値をfalse
        ELSE
            set @flag = false;
        END IF;
        select @flag;
    end
    //

    -- カートからすべての商品を削除する
    drop procedure if exists deleteCartProc//
    create procedure deleteCartProc(in in_customer_id int(8))
    begin
        UPDATE carts SET deleted_at = CURRENT_TIMESTAMP WHERE customer_id = in_customer_id;
    end
    //

    -- カートのレコード数を取得する
    drop procedure if exists getRowCountProc//
    create procedure getRowCountProc()
    begin
        SELECT COUNT(*) as count FROM carts;
    end
    //

    -- 重複商品の登録がないか検査する
    drop procedure if exists checkDuplicateItemProc//
    create procedure checkDuplicateItemProc(IN in_customer_id int(8), IN in_sku_id int(8))
    begin
        SELECT COUNT(*) as c FROM carts 
            WHERE sku_id = in_sku_id AND customer_id = in_customer_id
            AND
            deleted_at IS NULL;
    end
    //

delimiter ;