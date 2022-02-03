{include file='header.tpl'}
{block name=body}
    <div class="container">
        <h1>ログイン</h1>
        <p>会員のお客様</p>
        <p>メールアドレスとパスワードを入力してログインしてください</p>
        <div class="label">
            <form action="" method="POST">
                <div class="item">
                    <label for="email_address" >メールアドレス</label>
                    <input type="text" class="form-control" name="email_address" id="email_address" required>
                    {if !empty($err_msgs.email_address.0)}
                        <div class="err_msg">{{$err_msgs.email_address.0}}</div>
                    {/if}
                </div>
                <div class="item">
                    <label for="password">パスワード</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                    {if !empty($err_msgs.password.0)}
                        <div class="err_msg">{{$err_msgs.password.0}}</div>
                    {/if}
                </div>
                <div class="btns">
                    <input type="hidden" name="key" value="{$key}">
                    <button type="submit" class="btn_eno">ログイン</button>
                </div>
            </form>
            <div class="btns">
                <a href="/signup"><button class="btn_eno">アカウント作成</button></a>
                <a href="/passreset"><button class="btn_eno">パスワードリセット</button></a>
            </div>
        </div>
    </div>
{/block}
{include file='footer.tpl'}