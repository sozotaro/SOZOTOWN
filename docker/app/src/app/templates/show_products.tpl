{include file='header.tpl'}
<div class="container_2">
    {foreach from=$err_msgs item=$err_msg}
        <p>{$err_msg}</p>
    {/foreach}
    <form class="search" action="./products" method="POST">
        <input type="text" name="word" placeholder="キーワードを入力"/>
        <input type="submit" class="submit" value="検索する">
    </form>
    <nav class="nav-wrap">
        <div class="scroll-nav">
            <div class="nav_3-1">
                <ul class="nav_3-1_center">
                    <li>
                        <div class="nav_item">
                            <form action="./products" method="POST" name="form_1" id="form_1">
                                <input type="hidden" name="category" value="1">
                                <a href="javascript:document.form_1.submit()">アウター</a>
                            </form>
                        </div>
                    </li>
                    <li>
                        <div class="nav_item">
                            <form action="./products" method="POST" name="form_2" id="form_2">
                                <input type="hidden" name="category" value="2">
                                <a href="javascript:document.form_2.submit()">トップス</a>
                            </form>
                        </div>
                    </li>
                    <li>
                        <div class="nav_item">
                            <form action="./products" method="POST" name="form_3" id="form_3">
                                <input type="hidden" name="category" value="3">
                                <a href="javascript:document.form_3.submit()">ボトムス</a>
                            </form>
                        </div>
                    </li>
                    <li>
                        <div class="nav_item">
                            <form action="./products" method="POST" name="form_4" id="form_4">
                                <input type="hidden" name="category" value="4">
                                <a href="javascript:document.form_4.submit()">アクセサリー</a>
                            </form>
                        </div>
                    </li>
                    <li>
                        <div class="nav_item">
                            <form action="./products" method="POST" name="form_5" id="form_5">
                                <input type="hidden" name="category" value="5">
                                <a href="javascript:document.form_5.submit()">セットアップ</a>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="next-btn">〉</div>
    </nav>
    <ul>
        <li><a href="/">ホーム</a></li>
        {if $category_name}
            <li>＞</li>
            <li>{$category_name}</li>
        {/if}
        {if $search_word}
            <li>検索ワード：</li>
            <li>{$search_word}</li>
        {/if}
    </ul>
    <div class="item_all">
        {if $no_choice}
            <ul>
                <li>{$no_choice}</li>
            </ul>
        {/if}
        {if $results}
            {foreach from=$results item=result}
                <div class="item_main">
                    <div class="cart_img">
                        <a href="./product/{$result.product_id}">
                            {*エラー吐くので一旦コメントアウト*}
                            {*{html_image file='../../public/image/{$result.product_id}.png'}*}
{*                            imgタグで暫定対応*}
                            <img src="../public/image/item_img/{$result.product_id}.png" alt="商品画像">
                        </a>
                    </div>
                    <div class="item_list">
                        <h1>{$result.product_name}</h1>
                        <div class="item_price">
                            <p>{number_format($result.price, 0)}円</p>
                        </div>
                    </div>
                </div>
                <hr/>
            {/foreach}
        {/if}
    </div>
    <div class="pagination">
        {if $page_first}
            <a href="/products?p={$page_first}">最初へ</a>
        {/if}
        {if $page_last}
            <a href="/products?p={$page_last}" class="pre">戻る</a>
        {/if}
        {if $page_status}
            {foreach from=$page_status item=$p}
                <span><a href="/products?p={$p}">{$p}</a></span>
            {/foreach}
        {/if}
        {if $page_next}
            <a href="/products?p={$page_next}">次へ</a>
        {/if}
        {if $page_final}
            <a href="/products?p={$page_final}">最後へ</a>
        {/if}
    </div>
</div>
{include file='footer.tpl'}
