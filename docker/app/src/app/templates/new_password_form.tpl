{include file='header_simple.tpl'}
{block name=body}
    <div class="container_2">
        <h1>パスワード再設定</h1>
        <p>新しいパスワードを入力し、「パスワードを再設定する」ボタンをクリックしてください。</p>
        <div class="label5-3">
            <form class="form2" action="/newpass" method="POST">
                <div class="item">
                    <label for="password">新しいパスワード</label>
                    <input type="password" name="password" id="password">
                    {if !empty($err_msgs.password.0)}
                        <div class="err_msg">{{$err_msgs.password.0}}</div>
                    {/if}
                </div>
                <div class="item">
                    <label for="password_confirm">新しいパスワード(確認)</label>
                    <input type="password" name="password_confirm" id="password_confirm">
                    {if !empty($err_msgs.password_confirm.0)}
                        <div class="err_msg">{{$err_msgs.password_confirm.0}}</div>
                    {/if}
                </div>
                <div class="item">
                    <label for="id">認証コード</label>
                    <input type="password" name="id" id="id">
                    {if !empty($err_msgs.id.0)}
                        <div class="err_msg">{{$err_msgs.id.0}}</div>
                    {/if}
                </div>
                <input type="hidden" name="key" value="{$key}">
                <div class="btns">
                    <button class="btn_eno" type="submit">送信</button>
                </div>
            </form>
        </div>
    </div>
{/block}
{include file='footer.tpl'}