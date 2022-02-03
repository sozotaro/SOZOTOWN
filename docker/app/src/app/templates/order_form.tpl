{include file='header_simple.tpl'}
<div class="container_2">
    <h1>会員情報詳細</h1>
    <div class="form">
        <div class="item">
            ニックネーム:　{$smarty.session.nickname|default:'NO NAME'}
        </div>
        <div class="item">
            メールアドレス:　{$smarty.session.email_address|default:'example@example.com'}
        </div>
    </div>
    <hr>
    <form class="form2" method="POST">
        <h2>配送先住所を登録</h2>
        <div class="item">
            <label class="label2" for="name">名前</label>
            <input type="text" name="name" id="name" value="{$smarty.session.name|default:''}" required>
            {if !empty($err_msgs.name.0)}
                <div class="err_msg">{{$err_msgs.name.0}}</div>
            {/if}
        </div>
        <div class="item">
            <label class="label2" for="name_kana">フリガナ</label>
            <input type="text" name="name_kana" id="name_kana" value="{$smarty.session.name_kana|default:''}" required>
            {if !empty($err_msgs.name_kana.0)}
                <div class="err_msg">{{$err_msgs.name_kana.0}}</div>
            {/if}
        </div>
        <div class="item">
            <label class="label2" for="post_number">郵便番号</label>
            <input type="text" name="post_number" id="post_number" placeholder="〒　※ハイフンを入れず入力してください"
                   value="{$smarty.session.post_number|default:''}" required>
            {if !empty($err_msgs.post_number.0)}
                <div class="err_msg">{{$err_msgs.post_number.0}}</div>
            {/if}
        </div>
        <div class="item">
            <label class="label2" for="pref">都道府県</label>
            {html_options name=pref options=$prefs selected=$smarty.session.pref|default:'' required='' }
            {if !empty($err_msgs.pref.0)}
                <div class="err_msg">{{$err_msgs.pref.0}}</div>
            {/if}
        </div>
        <div class="item">
            <label class="label2" for="city">市区町村</label>
            <input type="text" name="city" id="city" value="{$smarty.session.city|default:''}" required>
            {if !empty($err_msgs.city.0)}
                <div class="err_msg">{{$err_msgs.city.0}}</div>
            {/if}
        </div>
        <div class="item">
            <label class="label2" for="street">番地</label>
            <input type="text" name="street" id="street" value="{$smarty.session.street|default:''}" required>
            {if !empty($err_msgs.street.0)}
                <div class="err_msg">{{$err_msgs.street.0}}</div>
            {/if}
        </div>
        <div class="item">
            <label class="label2" for="building">建物名</label>
            <input type="text" name="building" id="building" value="{$smarty.session.building|default:''}">
            {if !empty($err_msgs.building.0)}
                <div class="err_msg">{{$err_msgs.building.0}}</div>
            {/if}
        </div>
        <div class="item">
            <label class="label2" for="telephone_number">電話番号</label>
            <input type="tel" name="telephone_number" id="telephone_number" placeholder="※ハイフンを入れず入力してください"
                   value="{$smarty.session.telephone_number|default:''}" required>
            {if !empty($err_msgs.telephone_number.0)}
                <div class="err_msg">{{$err_msgs.telephone_number.0}}</div>
            {/if}
        </div>
        <div class="btns">
            <button class="btn_eno" type="button" onclick="history.back()">戻る</button>
            <button class="btn_eno" type="submit" formaction="/order/confirm">確認に進む</button>
        </div>
    </form>
</div>
{include file='footer.tpl'}