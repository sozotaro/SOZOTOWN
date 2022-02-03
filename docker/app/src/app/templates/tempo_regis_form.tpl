{include file='header_simple.tpl'}
{block name=body}
    <div class="container_2">
        <h1>アカウント登録画面</h1>
        <form action="/signup" method="POST" class="form2">
            <div class="item">
                <label for="email_address" class="label2">メールアドレス<span class="required_msg">[必須]</span></label>
                <input type="email" class="form-control" name="email_address" id="email_address" required>
                {if !empty($err_msgs.email_address.0)}
                    <div class="err_msg">{{$err_msgs.email_address.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label for="email_address_confirm" class="label2">メールアドレス(確認)<span class="required_msg">[必須]</span></label>
                <input type="email" class="form-control" name="email_address_confirm" id="email_address_confirm" required>
                {if !empty($err_msgs.email_address_confirm.0)}
                    <div class="err_msg">{{$err_msgs.email_address_confirm.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label for="password" class="label2">パスワード<span class="required_msg">[必須]</span></label>
                <input type="password" class="form-control" name="password" id="password" required>
                <p class="validate_msg">半角英数字、記号([-],[_]のみ使用可能) 8文字以上</p>
                {if !empty($err_msgs.password.0)}
                    <div class="err_msg">{{$err_msgs.password.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label for="password_confirm" class="label2">パスワード(確認)<span class="required_msg">[必須]</span></label>
                <input type="password" class="form-control" name="password_confirm" id="password_confirm" required>
                {if !empty($err_msgs.password_confirm.0)}
                    <div class="err_msg">{{$err_msgs.password_confirm.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label for="nickname" class="label2">ニックネーム<span class="required_msg">[必須]</span></label>
                <input type="text" class="form-control" name="nickname" id="nickname" required>
                <p class="validate_msg">半角英数字、記号([-],[_]のみ使用可能) 12文字以内</p>
                {if !empty($err_msgs.nickname.0)}
                    <div class="err_msg">{{$err_msgs.nickname.0}}</div>
                {/if}
            </div>
            <div class="btnArea1-2">
                <input type="hidden" name="key" value="{$key}">
                <button class="btn_eno" type="submit">登録する</button>
                <button class="btn_eno" type="button" onclick="location.href='/login'">戻る</button>
            </div>
        </form>
        <div class="btnArea1-2-1">
            <a href="/login">
                <button class="btn_eno">アカウントをお持ちの方はこちら</button>
            </a>
        </div>
    </div>
{/block}
{include file='footer.tpl'}