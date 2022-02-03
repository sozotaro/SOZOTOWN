{include file='header_simple.tpl'}
{block name=body}
    <div class="container">
      <img src="../public/image/ex_icon.png" alt="" />
      <div class="setA">
        <h1>仮登録URLをメールでお送りしました。</h1>
        <p>
          お手続きメールの有効期限は24時間です。<br />
          入力したメールアドレス宛にメールが届いているか確認ください。
        </p>
      </div>
      <div class="caption">
        <img src="../public/image/qu_icon.png" width="40px" alt="" />
        <span>メールが届かない場合は迷惑メール設定をご確認ください。</span>
      </div>
      <div class="btnArea">
        <a href="/login"><button>ログイン画面</button>
      </div>
    </div>
{/block}
{include file='footer.tpl'}