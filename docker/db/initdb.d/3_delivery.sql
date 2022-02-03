USE sozo;

SET CHARACTER_SET_CLIENT = utf8;
SET CHARACTER_SET_CONNECTION = utf8;

-- 複数SQL定義のために、SQLの区切り文字を ; から // に変更
DELIMITER //


-- 配送先マスタ用プロシージャ
    -- 会員情報の取得
    drop procedure if exists getDeliveryProc//
    create procedure getDeliveryProc(in in_customer_id int(8))
    begin
        -- 取得処理
        select name, name_kana, telephone_number, post_number, area_name, address from deliveries join areas on deliveries.area_id = areas.area_id where customer_id = in_customer_id;
    end
    //

    -- 会員情報の変更
    -- post_number も入れていい？
    -- delivery_id もいる？
    drop procedure if exists updateDeliveryProc//
    create procedure updateDeliveryProc(in in_delivery_id int(8), in in_customer_id int(8), in in_name varchar(20), in in_name_kana varchar(40), in in_telephone_number int(14), in in_post_number int(8), in in_area_id int(2), in in_address varchar(120))
    begin
        -- エラー発生時用のハンドラ
        declare EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
        begin
            set @flag = false;
        end;
        -- エラー発生前の返り値宣言
        set @flag = true;

        -- 更新対象が存在するかの確認
        select count(*) into @count from deliveries where customer_id = in_customer_id and delivery_id = in_delivery_id;

        -- 対象が存在する場合のみ更新処理
        if @count >= 1 then
            update deliveries set name = in_name, name_kana = in_name_kana, telephone_number = in_telephone_number, post_number = in_post_number, area_id = in_area_id, address = in_address where customer_id = in_customer_id and delivery_id = in_delivery_id;
        else
            set @flag = false;
        end if;
       -- 返り値の出力
        select @flag;
    end
    //

delimiter ;