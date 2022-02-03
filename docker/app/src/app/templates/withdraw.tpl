{include file='header_simple.tpl'}
{block name=body}
    <div class="container">
        <h1>退会手続</h1>
        <p class="confirmation">確認メッセージ</p>
        <p>本当に退会してもよろしいですか？</p>
        <div class="btnArea">
            {*<button><a href="/mypage">戻る</a></button>*}
            {*<button><a href="/withdrawed">退会</a></button>*}
            {*↑処理どっちかに↓*}
            <button onclick="location.href='/mypage'">戻る</button>
            <button onclick="location.href='/withdrawed'">退会</button>
        </div>
    </div>
{/block}
{include file='footer.tpl'}