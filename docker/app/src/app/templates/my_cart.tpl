{include file='header.tpl'}
<div class="container_2">
    <h1>カート内</h1>
    {if empty($results)}
        <div class="item">
            <h2>カートは空です</h2>
        </div>
        <div class="btns">
            <button class="btn_eno" onclick="location.href='/'">TOPに戻る</button>
        </div>
    {else}
        {if isset($err_msgs.count)}
            <p>{$err_msgs.count}</p>
        {/if}
        <form method="POST">
            <input type="hidden" name="key" id="key" value="{$key}">
            {$sub_total = 0}
            {foreach from=$results item=result}
                <div class="cart_main">
                    <div class="cart_img">
                        <img src="../public/image/item_img/1.png" alt="">
                        {*エラー吐くので一旦コメントアウト*}
                        {*<a href="./product/{$result.product_id}">*}
                        {*{html_image file='../../public/image/{$result.product_id}.png'}*}
                    </div>
                </div>
                <div class="cart_ex">
                    <h2>{$result.product_name}</h2>
                    <p>{$result.product_explanation}</p>
                    <div class="size_condition">
                        <p>サイズ:{$result.size_name}</p>
                        <p>状態:{$result.condition_name|substr:0:1}</p>
                    </div>
                    <div class="price_btns">
                        <p>¥{number_format($result.price)}(税込)</p>
                        <button class="btn_eno" type="submit" formaction="./cart/del/{$result.cart_id}">削除</button>
                    </div>
                </div>
                {math equation=$sub_total + $result.price assign='sub_total'}
            {/foreach}
            <div class="fixed_btn">
                <p>合計金額 ¥{number_format($subtotal)}(税込)</p>
                <button class="btn_eno" formaction="./order">ご注文手続きへ</button>
            </div>
        </form>
    {/if}
</div>
{include file='footer.tpl'}