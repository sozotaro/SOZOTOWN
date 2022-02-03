USE sozo;

SET CHARACTER_SET_CLIENT = utf8;
SET CHARACTER_SET_CONNECTION = utf8;

-- 複数SQL定義のために、SQLの区切り文字を ; から // に変更
DELIMITER //

-- 顧客マスタ用プロシージャ


    -- メールアドレス重複確認
    drop procedure if exists countEmailProc//
    create procedure countEmailProc(IN in_email_address varchar(256))
    begin
        SELECT COUNT(*) as count FROM customers WHERE email_address = in_email_address;
    end
    //

    -- 顧客テーブル&仮登録テーブルにINSERT
    drop procedure if exists customerInsertProc//
    create procedure customerInsertProc(IN in_email_address varchar(256), in in_password varchar(60), IN in_nickname varchar(128), in in_access_token char(8), IN in_password_token varchar(255))
    begin
        -- customers テーブルにinsert
        INSERT INTO customers(email_address, password, nickname) VALUES (in_email_address, in_password, in_nickname);

        -- 登録メールアドレスのcustomer_id 取得
        SELECT customer_id into @customer_id FROM customers WHERE email_address = in_email_address;

        -- 仮登録テーブルにinsert
        INSERT INTO temporary_registrations(customer_id, access_token, password_token) 
            VALUES (@customer_id, in_access_token, in_password_token);
    end
    //

    -- 仮登録データ取得
    drop procedure if exists tempoRegisSelectProc//
    create procedure tempoRegisSelectProc(in in_access_token char(8))
    begin
        SELECT customer_id, password_token, temporary_registration_at FROM temporary_registrations WHERE access_token = in_access_token AND temporary_registration_at IS NOT NULL;
    end
    //

    -- 会員登録処理&ニックネーム取得
    drop procedure if exists entryDateAndSelectNicknameProc//
    create procedure entryDateAndSelectNicknameProc(IN in_customer_id int(8))
    begin
        -- 入力されたcustomer_id が customers に登録されているか確認
        select customer_id into @customer_id from customers where customer_id = in_customer_id;
        IF @customer_id > 0 then
            -- customers テーブルのcreated_at を更新
            UPDATE customers SET created_at = current_timestamp WHERE customer_id = in_customer_id;

            -- 仮登録テーブルのdatetime をnullに更新
            UPDATE temporary_registrations SET temporary_registration_at = NULL WHERE  customer_id = in_customer_id;

            -- ニックネーム取得
            SELECT nickname FROM customers WHERE customer_id = in_customer_id;
        else
            set @return = null;
            select @return;
        end if;
    end
    //

    -- メールアドレスからログインデータ取得
    drop procedure if exists getLogindataByEmailProc//
    create procedure getLogindataByEmailProc(IN in_email_address varchar(256))
    begin
        SELECT customer_id, password, nickname, created_at, withdrawed_at FROM customers WHERE email_address = in_email_address;
    end
    //


    -- ログインエラーINSERT
    drop procedure if exists createLoginFailsProc//
    create procedure createLoginFailsProc(IN in_customer_id int(8))
    begin
        -- login_errors テーブルにinsert
        INSERT INTO login_errors(customer_id) VALUES (in_customer_id);
    end
    //

    -- ログインロック確認
    drop procedure if exists isValidUserProc//
    create procedure isValidUserProc(IN in_customer_id int(8))
    begin
        select count(*) AS count from login_errors WHERE customer_id = in_customer_id AND login_error_history_at > CURRENT_TIMESTAMP + INTERVAL -3 MINUTE;
    end
    //

    -- ログインエラー消去
    drop procedure if exists clearFailsProc//
    create procedure clearFailsProc(IN in_customer_id int(8))
    begin
        -- login_errors テーブルにからdelete
        delete from login_errors where customer_id = in_customer_id;
    end
    //


    -- メールアドレス取得
    drop procedure if exists getEmailProc//
    create procedure getEmailProc(IN in_customer_id int(8))
    begin
        SELECT email_address FROM customers WHERE customer_id = in_customer_id;
    end
    //

    -- 会員情報取得
    -- 前半は、getEmailProcと同様なので省略。そっちを使用すること
    drop procedure if exists getDeliveryProc//
    create procedure getDeliveryProc(IN in_customer_id int(8))
    begin
        SELECT * FROM  deliveries WHERE customer_id = in_customer_id;
    end
    //

    -- customers更新処理
    drop procedure if exists updateCustomerProc//
    create procedure updateCustomerProc(IN in_customer_id int(8), IN in_email_address varchar(256))
    begin
        SELECT COUNT(*) FROM customers WHERE email_address = in_email_address AND customer_id != in_customer_id;
    end
    //

    -- customers更新処理(新nickname取得)
    drop procedure if exists updateCustomerProc2//
    create procedure updateCustomerProc2(IN in_customer_id int(8), IN in_email_address varchar(256), in_nickname varchar(128))
    begin
        UPDATE customers SET email_address = in_email_address, nickname = in_nickname WHERE customer_id = in_customer_id;
        SELECT nickname FROM customers WHERE customer_id = in_customer_id;
    end
    //


    -- deliveries更新処理
    drop procedure if exists updateDeliveryProc//
    create procedure updateDeliveryProc(in in_delivery_id int(8), in in_customer_id int(8), in in_name varchar(20), in in_name_kana varchar(40), in in_telephone_number int(14), in in_post_number int(8), in in_area_id int(2), in in_address varchar(120))
    begin
        SELECT COUNT(*) into @count FROM deliveries WHERE customer_id = in_customer_id;

        if @count >= 1 then
            UPDATE deliveries SET 
                name = in_name, 
                name_kana = in_name_kana, 
                telephone_number = in_telephone_number, 
                post_number =in_post_number, 
                area_id = in_area_id, 
                address = in_address 
                WHERE customer_id = in_customer_id;
        else
            INSERT INTO deliveries
                (customer_id, name, name_kana, telephone_number, post_number, area_id, address)
                VALUES (in_customer_id, in_name, in_name_kana, in_telephone_number, in_post_number, in_area_id, in_address);
        end if;

    end
    //

    -- 退会処理
    drop procedure if exists withdrawedUpdateProc//
    create procedure withdrawedUpdateProc(IN in_customer_id int(8))
    begin
        UPDATE customers SET withdrawed_at = current_timestamp WHERE customer_id = in_customer_id;
        SELECT email_address FROM customers WHERE customer_id = in_customer_id;
    end
    //

    -- パスワード再設定
    -- DBに登録されているpassword の取得
    drop procedure if exists setPasswordReconfigureProc//
    create procedure setPasswordReconfigureProc(IN in_customer_id int(8))
    begin
        SELECT password FROM customers WHERE customer_id = in_customer_id;
    end
    //

    -- パスワード再設定
    drop procedure if exists setPasswordReconfigureProc2//
    create procedure setPasswordReconfigureProc2(IN in_customer_id int(8), in in_password varchar(60))
    begin
        UPDATE customers SET password = in_password WHERE customer_id = in_customer_id;
    end
    //

    -- メールアドレス確認
    drop procedure if exists selectEmailAndwithdrawedByEmailProc//
    create procedure selectEmailAndwithdrawedByEmailProc(IN in_email_address varchar(256))
    begin
        SELECT email_address, withdrawed_at FROM customers WHERE email_address = in_email_address;
    end
    //

    -- パスワードトークン生成&INSER
    drop procedure if exists passwordTokenInsertProc//
    create procedure passwordTokenInsertProc(IN in_email_address varchar(256), IN in_password_token varchar(255))
    begin
        UPDATE customers SET password_token = in_password_token, reseted_at = current_timestamp WHERE email_address = in_email_address;
    end
    //

    -- パスワードトークンチェック
    drop procedure if exists passwordTokenCheckProc//
    create procedure passwordTokenCheckProc(IN in_email_address varchar(256))
    begin
        SELECT password_token, reseted_at FROM customers  WHERE email_address = in_email_address;
    end
    //

    -- パスワード再設定(リセット)
    drop procedure if exists newPasswordSetProc//
    create procedure newPasswordSetProc(IN in_email_address varchar(256), IN in_password varchar(60))
    begin
        UPDATE customers SET password = in_password, password_token = NULL, reseted_at = NULL WHERE email_address = in_email_address;
    end
    //

    -- アドレス、ニックネームの変更
    drop procedure if exists updateCustomerProc//
    create procedure updateCustomerProc(IN in_customer_id int(8), IN in_email_address varchar(256), IN in_nickname varchar(128))
    begin
        declare EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
        begin
            set @flag = false;
        end;
        -- エラー発生前の返り値宣言
        set @flag = true;

        -- 更新対象が存在するかの確認
        select count(*) into @count from customers where customer_id = in_customer_id;

        -- 対象が存在する場合のみ更新処理
        if @count >= 1 then
            update customers set email_address = in_email_address, nickname = in_nickname where customer_id = in_customer_id;
        else
            set @flag = false;
        end if;
        select @flag;
    end
    //

delimiter ;