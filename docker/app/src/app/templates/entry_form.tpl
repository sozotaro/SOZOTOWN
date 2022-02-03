{block name=body}
    <h1>会員登録</h1>

    

        <form action="/entry" method="post">
        <label for="password">パスワード</label>
        <input type="password" name="password" id="password">
        {if !empty($err_msgs.password.0)}
            <div class="err_msg">{{$err_msgs.password.0}}</div>
        {/if}
        <br>
        <input type="hidden" name="key" value="{$key}">
        <button type="submit">送信</button>
        </form> 
{/block}