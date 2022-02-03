{include file='header_simple.tpl'}
{block name=body}
    <div class="container">
        <hr>
        <p class="subtitle6-1">新規会員登録が完了しました！</p>
        <div class="logo6-1">
            <img src="../public/image/check_mark.png" alt="">
        </div>
        <p>
            ご登録いただきありがとうございます、{{$nickname}}さん！
        </p>
        <div class="btnArea">
            <button onclick="location.href='/login'">ログイン画面</button>
        </div>
    </div>
{/block}
{include file='footer.tpl'}