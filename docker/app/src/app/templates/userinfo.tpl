{include file='header.tpl'}
{block name=body}
<div class="container_2">
        <h1>会員情報</h1>
        {*
        {if $err_msgs}
            <ul>
                {foreach from=$err_msgs item=err_msg}
                    <li>{{$err_msg}}</li>
                {/foreach}
            </ul>
        {/if}
        *}
        {if !empty($err_msgs.resultC.0)}
            <div>{{$err_msgs.resultC.0}}</div>
        {/if}
        <form class="form" action="/userinfoedC" method="POST">
            <div class="item">
                <label class="label" for="nickname">ニックネーム:</label>
                <input class="label" type="text" name="nickname" id="nickname" value="{{$nickname}}">
                {if !empty($err_msgs.nickname.0)}
                    <div>{{$err_msgs.nickname.0}}</div>
                {/if}
            </div>
            <div class="form">
                <label class="label" for="email_address">メールアドレス:</label>
                <input class="label" type="text" name="email_address" id="email_address" value="{{$email_address}}">
                {if !empty($err_msgs.email_address.0)}
                    <div>{{$err_msgs.email_address.0}}</div>
                {/if}
            </div>
            <input type="hidden" name="key" value="{$key}">
            <div class="btns">
                <button class="btn_eno">編集</button>
                <button class="btn_eno" type="submit">確定</button>
            </div>
        </form>
        <hr>
        <form action="/userinfoedD" class="form2" method="POST">
            <h2>会員情報詳細</h2>
            {if !empty($err_msgs.resultD.0)}
                <div>{{$err_msgs.resultD.0}}</div>
            {/if}

            <div class="item">
                <label class="label2" for="name">名前:</label>
                <input type="text" name="name" id="name" value="{{$name}}">
                {if !empty($err_msgs.name.0)}
                    <div>{{$err_msgs.name.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label class="label2" for="name_kana">フリガナ:</label>
                <input type="text" name="name_kana" id="name_kana" value="{{$name_kana}}">
                {if !empty($err_msgs.name_kana.0)}
                    <div>{{$err_msgs.name_kana.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label class="label2" for="post_number">郵便番号:</label>
                <input type="text" name="post_number" id="post_number" value="{{$post_number}}"
                       placeholder="※ハイフンを入れず入力してください">
                {if !empty($err_msgs.post_number.0)}
                    <div>{{$err_msgs.post_number.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label class="label2" for="pref">都道府県</label>
                {html_options name=pref options=$prefs selected=$area_id required='' }
                {if !empty($err_msgs.pref.0)}
                    <div>{{$err_msgs.pref.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label class="label2" for="city">市区町村:</label>
                <input type="text" name="city" id="city" value="{{$city}}">
                {if !empty($err_msgs.city.0)}
                    <div>{{$err_msgs.city.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label class="label2" for="street">番地:</label>
                <input type="text" name="street" id="street" value="{{$street}}">
                {if !empty($err_msgs.street.0)}
                    <div>{{$err_msgs.street.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label class="label2" for="building">マンション名:</label>
                <input type="text" name="building" id="building" value="{{$building}}">
                {if !empty($err_msgs.building.0)}
                    <div>{{$err_msgs.building.0}}</div>
                {/if}
            </div>
            <div class="item">
                <label class="label2" for="telephone_number">電話番号:</label>
                <input type="tel" name="telephone_number" id="telephone_number" value="{{$telephone_number}}"
                       placeholder="※ハイフンを入れず入力してください">
                {if !empty($err_msgs.telephone_number.0)}
                    <div>{{$err_msgs.telephone_number.0}}</div>
                {/if}
            </div>
            <div class="btns">
                <input type="hidden" name="key" value="{$key}">
                <button class="btn_eno">編集</button>
                <button class="btn_eno" type="submit">確定</button>
                <button type="button" class="btn_eno" onclick="history.back()">戻る</button>
            </div>
        </form>
</div>
{/block}
{include file='footer.tpl'}