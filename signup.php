<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//post送信されていた場合
if(!empty($_POST)){
  
  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  
  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');
  
  if(empty($err_msg)){
    
    //emailバリデーションチェック
    validEmail($email, 'email');
    validMaxLen($email, 'email');
    validEmailDup($email);
    
    //パスワードバリデーションチェック
    validHalf($pass, 'pass');
    validMaxLen($pass, 'pass');
    validMinLen($pass, 'pass');
    
    if(empty($err_msg)){
      validMatch($pass, $pass_re, 'pass_re');
      
      if(empty($err_msg)){
        
        //例外処理
        try {
          //DBへ接続
          $dbh = dbConnect();
          //SQL文作成
          $sql = 'INSERT INTO users (email,password,login_time,create_date) VALUES(:email,:pass,:login_time,:create_date)';
          $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));
          //クエリ実行
          $stmt = queryPost($dbh, $sql, $data);
          
          //クエリ成功の場合
          if($stmt){
            //ログイン有効期限（デフォルトを１時間とする）
            $sesLimit = 60*60;
            //最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            //ユーザーIDを格納
            $_SESSION['user_id'] = $dbh->lastInsertId();
            
            debug('セッション変数の中身：'.print_r($_SESSION,true));
            
            header("Location:completed.php"); //登録完了ページへ
          }
          
        } catch (Exception $e) {
          error_log('エラー発生：'.$e->getMessage());
          $err_msg['common'] = MSG03;
        }
        
      }
    }
  }
}
?>
<?php
$siteTitle = 'ユーザー登録';
require ('head.php');
?>

<body>
  
 <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <div class="form-title">
      <h2>ユーザー登録</h2>
      <p>以下の項目にご記入の上「登録する」ボタンを押してください</p>
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
              <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
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
              <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
            </div>
          </label>
          <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err' ?>">
            <div class="input-msg">
              <span class="input-title">パスワード</span>
              <span class="input-title-sub">(再入力）</span>
              <span class="area-msg">
                <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];?>
              </span>
            </div>
            <div class="input-pass">
              <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <input type="submit" class="btn" value="登録する">
        </div>
      </form>
    </div>
  </section>
  
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>