{include file='header.tpl'}
<div class="container">
    <div class="subTitle">
        <h1>購入履歴</h1>
        {if empty($orders)}
            <h2>購入履歴はありません</h2>
        {else}
            {foreach from=$orders item='order'}
                <hr>
                {*imgタグは本番環境で修正必要アリ*}
                <img src="../public/image/item_img/1.png" alt="">
                {*修正ここまで*}
                <hr>
                <div class="itemDetail">
                    <h1><a href="/product/{$order.product_id}">{$order.product_name}</a></h1>
                    <p>¥{$order.subtotal}(税込)</p>
                    <p>{$order.product_explanation}</p>
                    <p>
                        サイズ:{$order.size_name}
                        <br>
                        コンディション:{$order.condition_name|substr:0:1}
                    </p>
                </div>
            {/foreach}
        {/if}
        <div class="btnArea">
            <button onclick="history.back()">戻る</button>
        </div>
    </div>
</div>
{include file='footer.tpl'}