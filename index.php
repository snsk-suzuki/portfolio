<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
?>

<?php
$siteTitle = 'Fitness Record';
require ('head.php');
?>

<body>
  
 <!-- ヘッダー -->
  <?php
  require('headerNoMenu.php');
  ?>
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <div class="form-title">
      <h2>ポートフォリオを見て頂きありがとうございます！</h2>
    </div>
    <!-- メイン -->
    <section id="main">
      <div class="top">
        <p>筋トレを記録するサービスを作りました</p>
        <p>実装した機能</p>
        <ul>
          <li>メニューバーの「トレーニングを記録する」から筋トレの種目、重量、回数を記録できます。</li>
          <li>マイページにて記録した筋トレの履歴が閲覧でき、編集や削除ができます。また種目や日付順でソートができます。</li>
          <li>ユーザー登録、退会機能</li>
          <li>ログイン、ログアウト機能</li>
          <li>ログイン認証機能</li>
          <li>パスワードリマインダー機能</li>
          <li>画像投稿機能</li>
          <li>ページング機能</li>
        </ul>
        <p>スマホのみレスポンシブ化済みです。</p>
        <p>HTML CSS javascript jQuery PHP MySQLを使用しています。</p>
        <p>ログインページにてテスト用のメールアドレスとパスワードを用意してありますのでご利用ください。（機能制限あり）</p>
        <p>全機能を確認したい方はお手数ですがメールアドレスを用意して頂き、ユーザー登録をお願いします。（パスワードリマインダーのみ利用できません）</p>
      </div>
    </section>
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>
