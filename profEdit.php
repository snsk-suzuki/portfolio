<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
//DBからユーザーデータを取得
$dbFormData = getUser($_SESSION['user_id']);

debug('取得したユーザー情報：'.print_r($dbFormData,true));

//postされていた場合
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));
  
  //変数にユーザー情報を格納
  $username = (!empty($_POST['username'])) ? $_POST['username'] : null;
  $age = (!empty($_POST['age'])) ? $_POST['age'] : null;
  $height = (!empty($_POST['height'])) ? $_POST['height'] : null;
  $weight = (!empty($_POST['weight'])) ? $_POST['weight'] : null;
  $fat = (!empty($_POST['fat'])) ? $_POST['fat'] : null;
  $email = $_POST['email'];
  //画像をアップロードし、パスを格納
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic') : '';
  // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
  
  //DBの情報と入力情報が異なる場合にバリデーションを行う
  if($dbFormData['username'] !== $username){
    //名前の最大文字数チェック
    validMaxLen($username, 'username');
  }
  if($dbFormData['age'] !== $age){
    //年齢の最大文字数チェック
    validMaxLen($age, 'age');
    //年齢の半角数字チェック
    validNumber($age, 'age');
  }
  if($dbFormData['height'] !== $height){
    //身長の最大文字数チェック
    validMaxLen($height, 'height');
  }
  if($dbFormData['weight'] !== $weight){
    //体重の最大文字数チェック
    validMaxLen($weight, 'weight');
  }
  if($dbFormData['fat'] !== $fat){
    //体脂肪率の最大文字数チェック
    validMaxLen($fat, 'fat');
  }
  if($dbFormData['email'] !== $email){
    //emailの最大文字数チェック
    validMaxLen($email, 'email');
    if(empty($err_msg['email'])){
      //emailの重複チェック
      validEmailDup($email);
    }
    //emailの形式チェック
    validEmail($email, 'email');
    //emailの未入力チェック
    validRequired($email, 'email');
  }
  var_dump($err_msg);
  var_dump($height);
  if(empty($err_msg)){
    debug('バリデーションOKです。');
    
    //例外処理
    try{
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users SET username = :username, age = :age, height = :height, weight = :weight, fat = :fat, email = :email, pic = :pic WHERE id = :u_id';
      $data = array(':username' => $username, ':age' => $age, ':height' => $height, ':weight' => $weight, ':fat' => $fat, ':email' => $email, ':pic' => $pic, 'u_id' => $dbFormData['id']);
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      
      //クエリ成功の場合
      if($stmt){
        $_SESSION['msg_success'] = SUC01;
        debug('マイページへ遷移します');
        header("Location:mypage.php");
      }
      
    }catch(Exception $e){
      error_log('エラー発生:' .$e->getMessage());
      $err_msg['common'] = MSG03;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'プロフィール編集';
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
      <h2>プロフィール編集</h2>
      <p>以下の項目にご記入の上「変更する」ボタンを押してください</p>
      <p>テスト用ユーザーの方はメールアドレスの変更はできません</p>
      <p class="area-msg">
        <?php
        echo getErrMsg('common');
        ?>
      </p>
    </div>
  <!-- メイン -->
  <section id="main">
    <div class="form-container">
      <form action="" method="post" class="form" enctype="multipart/form-data">
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            <div class="input-msg">
              <span class="input-title">名前</span>
              <span class="area-msg">
                <?php
                echo getErrMsg('username');
                ?>
              </span>
            </div>
            <div class="input-email">
              <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
            <div class="input-msg">
              <span class="input-title">年齢</span>
              <span class="area-msg">
                <?php
                echo getErrMsg('age');
                ?>
              </span>
            </div>
            <div class="input-number">
              <input type="number" name="age" min="0" value="<?php echo getFormData('age'); ?>">
              <span>才</span>
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['height'])) echo 'err'; ?>">
            <div class="input-msg">
              <span class="input-title">身長</span>
              <span class="area-msg">
                <?php
                echo getErrMsg('height');
                ?>
              </span>
            </div>
            <div class="input-number">
              <input type="number" name="height" step ="0.1" min="0" value="<?php echo getFormData('height'); ?>">
              <span>cm</span>
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['weight'])) echo 'err'; ?>">
            <div class="input-msg">
              <span class="input-title">体重</span>
              <span class="area-msg">
                <?php
                echo getErrMsg('weight');
                ?>
              </span>
            </div>
            <div class="input-number">
              <input type="number" name="weight" step ="0.1" min="0" value="<?php echo getFormData('weight'); ?>">
              <span>kg</span>
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['fat'])) echo 'err'; ?>">
            <div class="input-msg">
              <span class="input-title">体脂肪率</span>
              <span class="area-msg">
                <?php
                echo getErrMsg('fat');
                ?>
              </span>
            </div>
            <div class="input-number">
              <input type="number" name="fat" step ="0.1" min="0" value="<?php echo getFormData('fat'); ?>">
              <span>%</span>
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            <div class="input-msg">
              <span class="input-title">メールアドレス</span>
              <span class="input-title-sub">(半角英数記号）</span>
              <span class="area-msg">
                <?php
                echo getErrMsg('email');
                ?>
              </span>
            </div>
            <div class="input-email">
              <input type="text" name="email" value="<?php echo getFormData('email'); ?>" <?php if($_SESSION['user_id'] == 6) echo 'readonly'; ?>>
            </div>
          </label>
        </div>
        <div class="form-fieldset">
          <label class="<?php if(!empty($err_msg['pic'])) echo 'err'; ?>">
            <div class="input-msg">
              <span class="input-title">プロフィール画像</span>
              <span class="area-msg">
                <?php
                echo getErrMsg('pic');
                ?>
              </span>
            </div>
            <div class="area-drop">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <input type="file" name="pic" class="input-file">
              <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
              ドラッグ＆ドロップ
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