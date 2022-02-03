{include file='header.tpl'}
<div class="container_2">
    <h1>NEWS</h1>
    <div class="main_imgBox">
        <img class="main_logo" src="../public/image/sozo_rogo.png" alt="SOL">
        <div class="main_img"
            style="background-image: url(https://f.uploader.xzy.pw/eu-prd/upload/20191108160744_61514c415a.jpg)"></div>
        <div class="main_img"
            style="background-image: url(https://f.uploader.xzy.pw/eu-prd/upload/20191108160418_5165755153.jpg)"></div>
        <div class="main_img"
            style="background-image: url(https://f.uploader.xzy.pw/eu-prd/upload/20191108160539_6f73696931.jpg)"></div>
        <div class="main_img"
            style="background-image: url(https://f.uploader.xzy.pw/eu-prd/upload/20191108160447_6f6d664c4f.jpg)"></div>
        <div class="main_img"
            style="background-image: url(https://f.uploader.xzy.pw/eu-prd/upload/20191108161055_374e67765a.jpg)"></div>
        <div class="main_img"
            style="background-image: url(https://f.uploader.xzy.pw/eu-prd/upload/20191108161128_42466f414a.jpg)"></div>
    </div>
    <h1>NEW ARRIVAL</h1>
    <div class="item_all">
        {if $results}
            {foreach from=$results item=result}
                <div class="item_main">
                    <a href="./product/{$result.product_id}">
                        {*{html_image file='../../public/image/{$result.product_id}.png'}*}
                        <img src="../public/image/item_img/{$result.product_id}.png" alt="商品画像" width="200px">
                    </a>
                </div>
                <div class="item_list">
                    <h1>{$result.product_name}</h1>
                    <div class="item_price">
                        <p>¥{number_format($result.price,0)}(税込)</p>
                    </div>
                </div>
            {/foreach}
        {/if}
    </div>
</div>
{include file='footer.tpl'}