{include file='header.tpl'}
<div class="container">
    <div class="subTitle">
        <h1>購入履歴</h1>
    </div>
    <div class="itemDetail2">
        {if empty($orders)}
            <h2>購入履歴はありません</h2>
        {else}
            <h2>これまでに購入された商品</h2>
            <ul id="target">
                <li><a href="history">購入日の新しい順</a></li>
                <li class="targetList"><a href="history?s=1">購入日の古い順</a></li>
            </ul>
            {foreach from=$orders item='order'}
                <div class="item">
                    <p>注文ID:<a href="/order/detail/{$order.order_histories_id}">0000{$order.order_histories_id}</a></p>
                    <p>注文日:{$order.ordered_at}</p>
                    <p>商品名{$order.product_name}
                        {if $order.count > 1}
                            ほか{$order.count - 1}点
                        {/if}
                    </p>
                </div>
                <div class="item">
                    <p>ご注文金額:{$order.amount}円(税込)</p>
                    <p>ご注文ステータス:{$order.status}</p>
                </div>
            {/foreach}
        {/if}
        <div class="btns">
            <a href="/mypage">
                <button class="btn_eno">戻る</button>
            </a>
        </div>
    </div>
    {if $page_first}
        <a href="/order/history?p={$page_first}{$s}">＜＜</a>
    {/if}
    {if $page_last}
        <a href="/order/history?p={$page_last}{$s}">＜</a>
    {/if}
    {if $page_status}
        {foreach from=$page_status item=page_s}
            <span><a href="/order/history?p={$page_s}{$s}">{$page_s}</a></span>
        {/foreach}
    {/if}
    {if $page_next}
        <a href="/order/history?p={$page_next}{$s}">＞</a>
    {/if}
    {if $page_final}
        <a href="/order/history?p={$page_final}{$s}">＞＞</a>
    {/if}
</div>
{include file='footer.tpl'}