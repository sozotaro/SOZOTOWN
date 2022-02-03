USE sozo;

SET CHARACTER_SET_CLIENT = utf8;
SET CHARACTER_SET_CONNECTION = utf8;

-- 複数SQL定義のために、SQLの区切り文字を ; から // に変更
DELIMITER //
    -- 商品一覧を取得
    drop procedure if exists getAllProductsProc//
    create procedure getAllProductsProc()
    begin
        select * from products;
    end
    //

    -- 商品詳細を取得
    drop procedure if exists getProductProc//
    create procedure getProductProc(IN in_product_id int(8))
    begin
        SELECT
            products_sku.sku_id
            , products.product_name
            , products.product_id
            , products.category_id
            , products.product_explanation
            , products_sku.price
            , sizes.size_name
            , conditions.condition_name
            , products_sku.stock_quantity
        FROM
            products_sku 
            LEFT JOIN products 
                ON products_sku.product_id = products.product_id
            LEFT JOIN sizes
                ON products_sku.size_id = sizes.size_id
            LEFT JOIN conditions
                ON products_sku.condition_id = conditions.condition_id
        WHERE
            products.product_id = in_product_id;
    end
    //

    -- 商品を検索する
    drop procedure if exists searchProductsProc//
    create procedure searchProductsProc(IN in_product_name varchar(50))
    begin
        select * from products where product_name LIKE in_product_name;
    end
    //


    -- 商品の最低価格を取得する
    drop procedure if exists getMinimumPriceProc//
    create procedure getMinimumPriceProc(IN in_product_id int(8))
    begin
        SELECT MIN(price) as min FROM products_sku WHERE product_id = in_product_id;
    end
    //

    -- 新しい商品マスタを登録する
    -- in_image_count のint(8)は適当
    drop procedure if exists createProductProc//
    create procedure createProductProc(IN in_product_name varchar(50), IN in_category_id int(4), IN in_product_explanation varchar(300), IN in_image_count int(8))
    begin
        INSERT INTO products (product_name, category_id, product_explanation, image_count) 
            VALUES (in_product_name, in_category_id, in_product_explanation, in_image_count);
    end
    //

    -- 在庫あり商品情報を取得
    drop procedure if exists GetByStocksProc//
    create procedure GetByStocksProc(in in_item_limit int(8), in in_page int(8), in in_catid int(8), in in_product_name varchar(256))
    begin
        -- カテゴリーID, 検索文が空でなければ検索条件を含める
        if in_catid != 0 then
            if in_product_name != null then
                -- カテゴリーID有り、検索文有り
                SELECT products_sku.product_id AS product_id, min(products_sku.price) AS price, products.product_name AS product_name
                    from products_sku 
                    join products on products_sku.product_id = products.product_id
                    where products_sku.stock_quantity > 0 
                    AND products.category_id = in_catid
                    AND products.product_name LIKE in_product_name
                    group by product_id ORDER BY products.product_id DESC LIMIT in_item_limit OFFSET in_page;
            else
                -- カテゴリーID有り、検索文無し
                SELECT products_sku.product_id AS product_id, min(products_sku.price) AS price, products.product_name AS product_name
                    from products_sku 
                    join products on products_sku.product_id = products.product_id
                    where products_sku.stock_quantity > 0 
                    AND products.category_id = in_catid
                    group by product_id ORDER BY products.product_id DESC LIMIT in_item_limit OFFSET in_page;
            end if;
        else
            if in_product_name != null then
                -- カテゴリーID無し、検索文有り
                SELECT products_sku.product_id AS product_id, min(products_sku.price) AS price, products.product_name AS product_name
                    from products_sku 
                    join products on products_sku.product_id = products.product_id
                    where products_sku.stock_quantity > 0 
                    AND products.product_name LIKE in_product_name
                    group by product_id ORDER BY products.product_id DESC LIMIT in_item_limit OFFSET in_page;
            else
                -- カテゴリーID無し、検索文無し
                SELECT products_sku.product_id AS product_id, min(products_sku.price) AS price, products.product_name AS product_name
                    from products_sku 
                    join products on products_sku.product_id = products.product_id
                    where products_sku.stock_quantity > 0 
                    group by product_id ORDER BY products.product_id DESC LIMIT in_item_limit OFFSET in_page;
            end if;
        end if;

    end
    //

    -- 在庫ありの商品ID総数を取得する
    drop procedure if exists GetQuantityByStocksProc//
    create procedure GetQuantityByStocksProc(in in_catid int(8), in in_product_name varchar(256))
    begin
        -- カテゴリーID, 検索文が空でなければ検索条件を含める
        if in_catid != 0 then
            if in_product_name != null then
                -- カテゴリーID有り、検索文有り
                SELECT COUNT(DISTINCT products.product_id ) as c
                    from products
                    join products_sku on products_sku.product_id = products.product_id
                    where products_sku.stock_quantity > 0
                    AND products.category_id = in_catid
                    AND products.product_name LIKE in_product_name;
            else
                -- カテゴリーID有り、検索文無し
                SELECT COUNT(DISTINCT products.product_id ) as c
                    from products
                    join products_sku on products_sku.product_id = products.product_id
                    where products_sku.stock_quantity > 0
                    AND products.category_id = in_catid;
            end if;
        else
            if in_product_name != null then
                -- カテゴリーID無し、検索文有り
                SELECT COUNT(DISTINCT products.product_id ) as c
                    from products
                    join products_sku on products_sku.product_id = products.product_id
                    where products_sku.stock_quantity > 0
                    AND products.product_name LIKE in_product_name;
            else
                -- カテゴリーID無し、検索文無し
                SELECT COUNT(DISTINCT products.product_id ) as c
                    from products
                    join products_sku on products_sku.product_id = products.product_id
                    where products_sku.stock_quantity > 0;
            end if;
        end if;
    end
    //

    -- 新商品一覧を取得する
    drop procedure if exists getNewProductsProc//
    create procedure getNewProductsProc(IN in_product_id int(8))
    begin
        SELECT products_sku.product_id AS product_id, min(products_sku.price) AS price, products.product_name AS product_name
            from products_sku 
            join products on products_sku.product_id = products.product_id
            where products_sku.stock_quantity > 0 
            group by product_id ORDER BY products.product_id DESC LIMIT 6;
    end
    //

delimiter ;