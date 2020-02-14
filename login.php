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
if(!empty($_POST)){
  debug('POST送信があります');
  
  //変数にPOST情報を格納
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;
  
  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  
  //Emailの形式チェック
  validEmail($email, 'email');
  //Emailの最大文字数チェック
  validMaxLen($email, 'email');
  
  //パスワードの半角英数字チェック
  validHalf($pass, 'pass');
  //パスワードの最大文字数チェック
  validMaxLen($pass, 'pass');
  //パスワードの最小文字数チェック
  validMinLen($pass, 'pass');
  
  if(empty($err_msg)){
    debug('バリデーションOKです');
    
    //例外処理
    try {
      //DBへ接続
      $dbh = dbConnect();
      //SQL文作成
      $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      //クエリ結果の値を取得
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      
      debug('クエリ結果の中身：'.print_r($result,true));
      
      //パスワード照合
      if(!empty($result) && password_verify($pass, $result['password'])){
        debug('パスワードがマッチしました');
        
        //ログイン有効期限（デフォルトは１時間）
        $sesLimit = 60*60;
        //最終ログイン日時を現在に
        $_SESSION['login_date'] = time();
        
        //ログイン保持にチェックがある場合
        if($pass_save){
          debug('ログイン保持にチェックがあります');
          
          //ログイン有効期限を３０日に設定
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        }else{
          debug('ログイン保持にチェックがありません');
          //ログイン有効期限を１時間に設定
          $_SESSION['login_limit'] = $sesLimit;
        }
        //ユーザーIDを格納
        $_SESSION['user_id'] = $result['id'];
        
        debug('セッション変数の中身：'.print_r($_SESSION,true));
        debug('マイページへ遷移');
        header("Location:mypage.php");
      }else{
        debug('パスワードがアンマッチです');
        $err_msg['common'] = MSG09;
      }
      
    }catch(Exception $e){
      error_log('エラー発生：'.$e->getMessage());
      $err_msg[common] = MSG03;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'ログイン';
require ('head.php');
?>

<body>
  
 <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <div class="form-title">
      <h2>ログイン</h2>
      <p>テスト用ユーザーを利用する方はこのままログインしてください</p>
      <p>以下の項目にご記入の上「ログイン」ボタンを押してください</p>
      <p class="area-msg">
        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
      </p>
      </div>
  <!-- メイン -->
  <section id="main">
   
    <div class="form-container">
      <form action="" method="post" class="form">
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['email'])) echo 'err' ?>">
            <div class="input-msg">
              <span class="input-title">メールアドレス</span>
              <span class="input-title-sub">(半角英数記号）</span>
              <span class="area-msg">
                <?php if(!empty($err_msg['email'])) echo $err_msg['email'];?>
              </span>
            </div>
            <div class="input-email">
              <input type="text" name="email" value="nakayama@muscle.com<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['pass'])) echo 'err' ?>">
            <div class="input-msg">
              <span class="input-title">パスワード</span>
              <span class="input-title-sub">(半角英数字６文字以上）</span>
              <span class="area-msg">
                <?php if(!empty($err_msg['pass'])) echo $err_msg['pass'];?>
              </span>
            </div>
            <div class="input-pass">
              <input type="password" name="pass" value="123456<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <input type="submit" class="btn" value="ログイン">
          <label class="checkbox">
            <input type="checkbox" name="pass_save">次回ログインを省略する
          </label>
          <div class="passremind">
          <a href="passRemindSend.php">パスワードを忘れた方はコチラ</a>
          </div>
        </div>
      </form>
    </div>
  </section>
  
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>