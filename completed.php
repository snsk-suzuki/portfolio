<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録完了ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

$siteTitle = 'ユーザー登録完了';
require('head.php');
?>


<body>
  
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <div class="form-title">
      <h2>ユーザー登録が完了しました</h2>
    </div>
  <section id="main">
    <a href="mypage.php" class="btn linkmypage">マイページへ</a>
  </section>
  </div>
  
 <!-- footer -->
  <?php
  require('footer.php');
  ?>