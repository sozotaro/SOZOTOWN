{include file='header_simple.tpl'}
<div class="container_2">
    <h1>注文内容確認</h1>
    <div class="ue">
        <p>カート内</p>
        <p>ご注文小計：{number_format($subtotal,0)|default:0}円</p>
    </div>
    <div class="cart_main">
        {$sub_total = 0}
        {foreach from=$carts item=cart}
            <div class="cart_img">
                {*{html_image file='../public/images/item_image/1.png'}*}
            </div>
            <div class="cart_ex">
                <h2>{$cart.product_name}</h2>
                <p>{$cart.product_explanation}</p>
            </div>
            <div class="size_condition">
                <p>サイズ:{$cart.size_name}</p>
                <p>状態:{substr($cart.condition_name,0,1)}</p>
                <p>数量:{$cart.quantity}</p>
            </div>
            <hr>
            {math equation=$sub_total + $cart.price assign='sub_total'}
        {/foreach}
    </div>
    <div class="howto_pay">
        <h1>支払い方法</h1>
        <h2>代引き</h2>
        <p>配送1件ごとに1,000円かかります。</p>
    </div>
    <div class="howto_pay">
        <h1>お支払い合計金額</h1>
        <h2>{number_format($smarty.session.amount)}円</h2>
    </div>
    <div class="howto_pay">
        <form class="form2" method="POST" action="/order/complete">
            <input type="hidden" name="key" id="key" value="{$key}">
            <input type="radio" name="deliver" id="deliver" value="same" checked><label for="deliver">注文住所と同じ</label>
            <input type="radio" name="deliver" id="deliver" value="different"><label for="deliver">それ以外の場合</label>
            <div class="item">
                <label class="label2" for="name">名前</label>
                <input type="text" name="name" id="name" value="{$smarty.session.name|default:''}" required>
            </div>
            <div class="item">
                <label class="label2" for="name_kana">フリガナ</label>
                <input type="text" name="name_kana" id="name_kana" value="{$smarty.session.name_kana|default:''}" required>
            </div>
            <div class="item">
                <label class="label2" for="post_number">郵便番号</label>
                <input type="text" name="post_number" id="post_number" placeholder="〒　※ハイフンを入れず入力してください"
                       value="{$smarty.session.post_number|default:''}" required>
            </div>
            <div class="item">
                <label class="label2" for="pref">都道府県</label>
                {*{html_options name=pref options=$prefs selected=$smarty.session.pref|default:'' required='' }*}
                {$pref}
            </div>
            <div class="item">
                <label class="label2" for="city">市区町村</label>
                <input type="text" name="city" id="city" value="{$smarty.session.city|default:''}" required>
            </div>
            <div class="item">
                <label class="label2" for="street">番地</label>
                <input type="text" name="street" id="street" value="{$smarty.session.street|default:''}" required>
            </div>
            <div class="item">
                <label class="label2" for="building">建物名</label>
                <input type="text" name="building" id="building" value="{$smarty.session.building|default:''}">
            </div>
            <div class="item">
                <label class="label2" for="telephone_number">電話番号</label>
                <input type="tel" name="telephone_number" id="telephone_number" placeholder="※ハイフンを入れず入力してください"
                       value="{$smarty.session.telephone_number|default:''}" required>
            </div>
            <div class="btns">
                <button class="btn_eno" type="submit" formaction="/order">戻る</button>
                <button class="btn_eno" type="submit">確定</button>
            </div>
        </form>
    </div>
</div>
{include file='footer.tpl'}