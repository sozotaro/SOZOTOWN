{include file='header.tpl'}
{block name=body}
    <div class="container_2">
        <h1>マイページ</h1>
        <div class="item">
            <p>ニックネーム:{{$nickname}}</p>
        </div>
        <button class="btn_eno" onclick="location.href='/logout'">ログアウト</button>
        <hr>
        <div class="item mypage">
            <a href="/order/history">
                <p>購入履歴</p>
            </a>
        </div>
        <hr>
        <a href="/userinfo">
            <div class="item mypage">
                <p>会員情報</p>
            </div>
        </a>
        <hr>
        <a href="/password">
            <div class="item mypage">
                <p>パスワード変更</p>
            </div>
        </a>
        <hr>
        <a href="/withdraw">
            <div class="item mypage">
                <p>退会の手続き</p>
            </div>
        </a>
    </div>
{/block}
{include file='footer.tpl'}