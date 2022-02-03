{include file='header_simple.tpl'}
{block name=body}
    <div class="container">
        <h1>本登録認証</h1>

        <p>登録をしたパスワードを入力してください。</p>
        <form action="/entry" method="POST">
            <div class="item">
                <label for="password">パスワード</label>
                <input type="password" name="password" id="password">
                {if !empty($err_msgs.password.0)}
                    <div class="err_msg">{{$err_msgs.password.0}}</div>
                {/if}
                <input type="hidden" name="key" value="{$key}">
            </div>
            <div class="btns">
                <button class="btn_eno" type="submit">送信</button>
            </div>
        </form>
    </div>
{/block}
{include file='footer.tpl'}