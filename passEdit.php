<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザーデータを取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData,true));

//post送信されていた場合
if(!empty($_POST) && $_SESSION['user_id'] !== '6'){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  
  //変数にユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];
  
  //未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');
  
  if(empty($err_msg)){
    debug('未入力チェックOK。');
    
    //古いパスワードのチェック
    validPass($pass_old, 'pass_old');
    //新しいパスワードのチェック
    validPass($pass_new, 'pass_new');
    
    //古いパスワードとDBパスワードを照合（DBに入っているデータと同じであれば、半角英数字チェックや最大文字数チェックは行わなくても問題ない）
    if(!password_verify($pass_old, $userData['password'])){
      $err_msg['pass_old'] = MSG12;
    }
    
    //新しいパスワードと古いパスワードが同じかチェック
    if($pass_old === $pass_new){
      $err_msg['pass_new'] = MSG13;
    }
    
    //パスワードとパスワード再入力が合っているかチェック（ログイン画面では最大、最小チェックもしていたがパスワードの方でチェックしているので実は必要ない）
    validMatch($pass_new, $pass_new_re, 'pass_new_re');
    
    if(empty($err_msg)){
      debug('バリデーションOK。');
      
      //例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'UPDATE users SET password = :pass WHERE id = :id';
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        // クエリ成功の場合
        if($stmt){
          $_SESSION['msg_success'] = SUC02;
          
          //メールを送信
          $username = ($userData['username']) ? $userData['username'] : '名無し';
          $from = 'fitnessrecord@mail.com';
          $to = $userData['email'];
          $subject = 'パスワード変更通知｜FitnessRecord';
          //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
          //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
          $comment = <<<EOT
{$username}　さん
パスワードが変更されました。
                      
////////////////////////////////////////
Fitness Record
E-mail fitnessrecord@mail.com
////////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);
          
          header("Location:mypage.php"); //マイページへ
        }
        
      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG03;
      }
    }
  }
}
?>
<?php
$siteTitle = 'パスワード変更';
require ('head.php');
?>

<body>
  
 <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  
<body class="page-2colum">
  
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <div class="form-title">
      <h2>パスワード変更</h2>
      <p>以下の項目にご記入の上「変更する」ボタンを押してください</p>
      <p>テスト用ユーザーの方は変更できません</p>
      <p class="area-msg">
        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
      </p>
    </div>
  <!-- メイン -->
  <section id="main">
    <div class="form-container">
      <form action="" method="post" class="form">
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err' ?>">
            <div class="input-msg">
              <span class="input-title">古いパスワード</span>
              <span class="area-msg">
                <?php
                echo getErrMsg('pass_old');
                ?>
              </span>
            </div>
            <div class="input-pass">
              <input type="password" name="pass_old" value="<?php echo getFormData('pass_old'); ?>">
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err' ?>">
            <div class="input-msg">
              <span class="input-title">新しいパスワード</span>
              <span class="area-msg">
                <?php if(!empty($err_msg['pass_new'])) echo $err_msg['pass_new'];?>
              </span>
            </div>
            <div class="input-pass">
              <input type="password" name="pass_new" value="<?php echo getFormData('pass_new'); ?>">
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err' ?>">
            <div class="input-msg">
              <span class="input-title">新しいパスワード（再入力）</span>
              <span class="area-msg">
                <?php
                echo getErrMsg('pass_new_re');
                ?>
              </span>
            </div>
            <div class="input-pass">
              <input type="password" name="pass_new_re" value="<?php echo getFormData('pass_new_re'); ?>">
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <input type="submit" class="btn" value="変更する">
        </div>
      </form>
    </div>
  </section>
  
  <!-- サイドバー -->
  <?php
  require('sidebar.php');
  ?>
  </div>
  
   <!-- footer -->
  <?php
  require('footer.php');
  ?>