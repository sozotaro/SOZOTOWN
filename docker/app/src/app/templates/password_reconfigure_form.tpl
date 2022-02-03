{include file='header_simple.tpl'}
{block name=body}
<div class="container">
<h1>パスワード変更</h1>
    <div class="label">
        <form class="form" action="" method="POST">
        <div class="item">
            <label for="password">現在のパスワード</label>
            <input type="password" name="password" id="password" required>
            {if !empty($err_msgs.password.0)}
                <div class="err_msg">{{$err_msgs.password.0}}</div>
            {/if}
        </div>
        <div class="item">
            <label for="new_password">新しいパスワード</label>
            <input type="password" name="new_password" id="new_password" required>
            <p class="validate_msg">半角英数字、記号([-],[_]のみ使用可能) 8文字以上</p>
            {if !empty($err_msgs.new_password.0)}
                <div class="err_msg">{{$err_msgs.new_password.0}}</div>
            {/if}
        </div>
        <div class="item">
            <label for="new_password_confirm">新しいパスワード(確認)</label>
            <input type="password" name="new_password_confirm" id="new_password_confirm" required>
            {if !empty($err_msgs.new_password_confirm.0)}
                <div class="err_msg">{{$err_msgs.new_password_confirm.0}}</div>
            {/if}
        </div>
        <br>
        <input type="hidden" name="key" value="{$key}">
            <div class="btns">
                <button class="btn_eno" type="submit">変更</button>
                <button class="btn_eno" type="button" onclick="location.href='/mypage'">戻る</button>
            </div>
        </form>
    </div>
</div>
{/block}
{include file='footer.tpl'}