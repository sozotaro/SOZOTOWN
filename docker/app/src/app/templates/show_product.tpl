{include file='header.tpl'}
<div class="container">
    <div class="subTitle">
        <h1>商品詳細</h1>
        <img src="../../public/image/item_img/{$results[0].product_id}.png" alt="商品画像" width="200px">
        <p>※画像はサンプル撮影のため、実際の商品と色味・使用が異なる場合がございます。</p>
    </div>
    <div class="itemDetail">
        <h1>{$results[0].product_name}</h1>
        <p>{number_format($minimum,0)}円(税込)</p>
        <p>{$results[0].product_explanation}</p>
        <div class="sizeSelect">
            <table>
                <tr>
                    <th>サイズ</th>
                    <th>状態</th>
                    <th>価格</th>
                    <th>在庫</th>
                    <th></th>
                </tr>
                {foreach from=$results item='result'}
                    <tr>
                        <td>{$result.size_name}</td>
                        <td>{$result.condition_name|substr:0:1}</td>
                        <td>{number_format($result.price,0)}円</td>
                        <td>
                            {if $result.stock_quantity >= 1}
                                ○
                            {else}
                                ×
                            {/if}
                        </td>
                        <td>
                            <form action="/cart" method="POST">
                                <input type="hidden" name="key" value="{$key}">
                                <input type="hidden" name="sku_id" value="{$result.sku_id}">
                                <input type="submit" value="カートに入れる">
                                {if $result.stock_quantity == 0}
                                    disabled
                                {/if}
                            </form>
                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
</div>
{include file='footer.tpl'}