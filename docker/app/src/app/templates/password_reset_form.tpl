{include file='header_simple.tpl'}
{block name=body}
    <div class="container">
        <h1>パスワード再設定</h1>
        <p>
            ご利用のメールアドレスを入力して下さい。<br>
            パスワード再設定のためのURLをお送りします。
        </p>
        <div class="label5-1">
            <form action="" method="POST">
                <label for="email_address">登録済メールアドレス</label>
                <input type="text" name="email_address" id="email_address">
                {if !empty($err_msgs.email_address.0)}
                    <div>{{$err_msgs.email_address.0}}</div>
                {/if}
                <input type="hidden" name="key" value="{$key}">
                <div class="btnArea">
                    <button type="submit">送信</button>
                    <button onclick="location.href='/login'">ログイン画面</button>
                </div>
            </form>
        </div>
    </div>
{/block}
{include file='footer.tpl'}
