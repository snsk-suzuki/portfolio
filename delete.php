<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　削除ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//GETデータを格納
$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
//DBからトレーニング情報を取得
$dbTraningData = (!empty($t_id)) ? getTraning($_SESSION['user_id'], $t_id) : '';
debug('削除するトレーニング'.print_r($dbTraningData, true));

if(!empty($dbTraningData)){
  debug('DB更新');
  //例外処理
try{
  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql = 'UPDATE traningedit SET delete_flg = 1 WHERE user_id = :u_id AND id = :t_id';
  $data = array(':u_id' => $_SESSION['user_id'], ':t_id' => $t_id);
  //クエリ実行
  $stmt = queryPost($dbh, $sql, $data);
  
  //クエリ成功の場合
  if($stmt){
    $_SESSION['msg_success'] = SUC05;
    debug('元のページへ遷移します');
    header("Location:mypage.php".appendGetParam(array('t_id')));
  }
  
}catch(Exception $e){
  error_log('エラー発生：'.$e->getMessage());
  $err_msg['common'] = MSG03;
}
  
}else{
  $_SESSION['msg_success'] = SUC06;
  debug('元のページへ遷移します');
  header("Location:mypage.php".appendGetParam(array('t_id')));
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = '削除ページ';
require ('head.php');
?>

<body>
  
 <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  <div id="contents" class="site-width">
    <?php echo getErrMsg('common'); ?>
  </div>
  
  <?php
  require('footer.php');
  ?>