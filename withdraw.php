<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
//post送信されていた場合
if(!empty($_POST) && $_SESSION['user_id'] !== '6'){
  debug('POST送信があります');
  //例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
    $data = array(':u_id' => $_SESSION['user_id']);
    //クエリ実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    
    //クエリ実行が成功した場合
    if($stmt1){
      //セッションを削除
      session_destroy();
      debug('セッション変数の中身：'.print_r($_SESSION,true));
      debug('トップページへ遷移します');
      header("Location:index.php");
    }else{
      debug('クエリが失敗しました');
      $err_msg['common'] = MSG03;
    }
    
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG03;
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '退会';
require('head.php');
?>

<body>
  
  <!-- ヘッダー -->
  <?php
  require('headerNoMenu.php');
  ?>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <div class="form-title">
      <h2>退会</h2>
      <p class="area-msg">
        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
      </p>
      <p>テスト用ユーザーの方は退会できません</p>
    </div>
  <!-- メイン -->
  <section id="main">
      <form action="" method="post" class="form">
        <input type="submit" class="btn btn-mid" value="退会する" name="submit">
      </form>
    <a href="mypage.php">マイページへ戻る</a>
  </section>
  
  </div>
  
  <!-- フッター -->
  <?php
  require('footer.php');
  ?>