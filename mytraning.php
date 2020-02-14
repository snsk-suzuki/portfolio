<?php

//共通変数・関数ファイルを読込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイトレーニングページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
//GETデータを格納
$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
//DBからユーザーデータを取得
$dbUserData =getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($dbUserData,true));
//DBからトレーニング情報を取得
$dbTraningData = (!empty($t_id)) ? getTraning($_SESSION['user_id'], $t_id) : '';
debug('取得したトレーニング情報：'.print_r($dbTraningData,true));
//新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbTraningData)) ? false : true;
//カテゴリ
$dbEventData = getEvent($_SESSION['user_id']);
debug('カテゴリ'.print_r($dbEventData,true));

//postされていた場合
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST,true));
  
  //変数にpost情報を格納
  $traning = $_POST['traning'];
  $rep = $_POST['rep'];
  $t_weight = $_POST['t_weight'];
  
  //更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  if(empty($dbTraningData)){
    //種目の未入力チェック
    validRequired($traning, 'traning');
    //種目の最大文字数チェック
    validMaxLen($traning, 'traning');
    //回数の未入力チェック
    foreach($rep as $key=>$val){
      $key .= 'rep';//重量と回数を区別するためキーに文字列を追加
      validRequired($val, $key);
    }
    //回数の最大文字数チェック
    foreach($rep as $key=>$val){
      $key .= 'rep';
      validMaxLen($val, $key);
    }
    //重量の未入力チェック
    foreach($t_weight as $key=>$val){
      $key .= 't_weight';
      validRequired($val, $key);
    }
    //重量の最大文字数チェック
    foreach($t_weight as $key=>$val){
      $key .= 't_weight';
      validMaxLen($val, $key);
    }
  }else{
    if($dbTraningData['traning'] !== $traning){
      //種目の未入力チェック
      validRequired($traning, 'traning');
      //種目の最大文字数チェック
      validMaxLen($traning, 'traning');
    }
    if($dbTraningData['rep']){
      //回数の未入力チェック
      foreach($rep as $key=>$val){
        $key .= 'rep';
        validRequired($val, $key);
      }
      //回数の最大文字数チェック
      foreach($rep as $key=>$val){
        $key .= 'rep';
        validMaxLen($val, $key);
      }
    }
    if($dbTraningData['t_weight']){
      //重量の未入力チェック
      foreach($t_weight as $key=>$val){
        $key .= 't_weight';
        validRequired($val, $key);
      }
      //重量の最大文字数チェック
      foreach($t_weight as $key=>$val){
        $key .= 't_weight';
        validMaxLen($val, $key);
      }
    }
  }
  if(empty($err_msg)){
    debug('バリデーションOKです。');
    
    //DBへ保存するため配列データをシリアライズ
    $rep = serialize($rep);
    $t_weight = serialize($t_weight);
    
    //例外処理
    try{
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      // 編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
      if($edit_flg){
        debug('DB更新です。');
        $sql = 'UPDATE traningedit SET traning = :traning, rep = :rep, t_weight = :t_weight WHERE user_id = :u_id AND id = :t_id';
        $data = array(':traning' => $traning, ':rep' => $rep, ':t_weight' => $t_weight, ':u_id' => $_SESSION['user_id'], ':t_id' => $t_id);
      }else{
        debug('DB新規登録です。');
        $sql = 'INSERT INTO traningedit (traning, rep, t_weight, user_id, create_date) VALUES (:traning, :rep, :t_weight, :u_id, :date)';
        $data = array(':traning' => $traning, ':rep' => $rep, ':t_weight' => $t_weight, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL：'.$sql);
      debug('流し込みデータ：'.print_r($data,true));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      
      // クエリ成功の場合
      if($stmt){
        //配列データをアンシリアライズ
        $rep = unserialize($rep);
        $t_weight = unserialize($t_weight);
        
        $_SESSION['msg_success'] = SUC04;
        debug('マイページへ遷移します。');
        header("Location:mypage.php".appendGetParam(array('t_id')));
      }
      
    }catch (Exception $e){
       error_log('エラー発生:' . $e->getMessage());
       $err_msg['common'] = MSG03;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = (!$edit_flg) ? 'トレーニング登録' : 'トレーニング編集';
require ('head.php');
?>
  
<body class="page-2colum">

 <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  
  <form action="" method="post">
    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      <div class="mytraning-prof">
        <div class="avatar-name">
          <img src="<?php echo showImg(sanitize($dbUserData['pic'])); ?> " alt="" class="avatar">
          <span class="user-name"><?php if(!empty($dbUserData['username'])){ echo sanitize($dbUserData['username']);}else{echo '名無し';} ?>さん</span>
        </div>
        <div class="user-info">
          <div class="bodysize">身長<br>
            <div class="input-number">
              <?php if(!empty($dbUserData['height'])){ echo sanitize($dbUserData['height']);}else{echo 'ーー';} ?>
              <span>cm</span>
            </div>
          </div>
          <div class="bodysize">体重<br>
            <div class="input-number">
              <?php if(!empty($dbUserData['weight'])){ echo sanitize($dbUserData['weight']);}else{echo 'ーー';} ?>
              <span>kg</span>
            </div>
          </div>
          <div class="bodysize">体脂肪率<br>
            <div class="input-number">
              <?php if(!empty($dbUserData['fat'])){ echo sanitize($dbUserData['fat']);}else{echo 'ーー';} ?>
              <span>%</span>
            </div>
          </div>
        </div>
      </div>
    <!-- メイン -->
    <section id="main">
      <div class="form-container">
        <div class="form">
          <div class="form-fieldset">
            <div class="create-date"><?php if($edit_flg) echo sanitize($dbTraningData['create_date']); ?></div>
            <div class="form-box">
              <div class="set">
                <div id="add-set">
                  <input type="button" class="btn" value="+">
                </div>
                <div id="delete-set">
                  <input type="button" class="btn" value="-">
                </div>
              </div>
              <div class="event">
                <label class="<?php if(!empty($err_msg['traning'])) echo 'err'; ?>">
                  <div class="input-msg">
                    <span class="input-title">種目</span>
                    <span class="area-msg">
                      <?php
                      echo getErrMsg('traning');
                      ?>
                    </span>
                  </div>
                  <div class="input-traning">
                    <input type="text" name="traning" value="<?php if(!empty($_POST)){ echo sanitize($traning); }elseif(!empty($dbTraningData)){ echo sanitize($dbTraningData['traning']);} ?>" ; placeholder="入力or選択">
                    <select>
                      <option value="" >選択</option>
                      <?php
                        foreach($dbEventData as $val){
                      ?>
                      <option value="<?php if(!empty($dbEventData)) echo sanitize($val['traning']); ?>" <?php if(getFormData('event',true) == $val['traning'] ){ echo 'selected'; } ?> >
                         <?php if(!empty($dbEventData)) echo sanitize($val['traning']); ?>
                      </option>
                      <?php
                        }
                      ?>
                    </select>
                  </div>
                </label>
              </div>
            </div>
            <div class="form-box">
              <div class="weight-rep">
                <label class="">
                  <div class="input-msg">
                    <span class="input-title">回数</span>
                  </div>
                  
                  <?php
                  if(!empty($_POST)){//バリデーションで引っかかった場合追加したフォームと値を残す処理(回数)
                    foreach($rep as $key=>$val){
                    $key .= 'rep';
                  ?>
                  <div id="rep-form" class="input-traning <?php if(!empty($err_msg[$key])) echo 'err'; ?>">
                    <div class="add-area-msg">
                    <?php
                    echo getErrMsg($key);
                    ?>
                    </div>
                    <input type="number" name="rep[]" step ="1" min="0" class="input-weight rep" value="<?php echo sanitize($val); ?>">
                    <select>
                      <option value="" >選択</option>
                      <?php 
                      for($i = 1; $i <= 100; $i++){
                        echo '<option value="'.$i.'">'.$i.'</option>';
                      }
                      ?>
                    </select>
                    <span>回</span>
                  </div>
                    <?php
                    }
                    ?>
                    
                  <?php
                  }elseif($edit_flg){//編集画面したいトレーニングのフォーム数と値を表示する処理(回数)
                    foreach(unserialize($dbTraningData['rep']) as $val2){
                    ?>
                    <div id="rep-form" class="input-traning">
                      <input type="number" name="rep[]" step ="1" min="0" class="input-weight rep" value="<?php echo sanitize($val2); ?>">
                      <select>
                        <option value="" >選択</option>
                        <?php 
                        for($i = 1; $i <= 100; $i++){
                          echo '<option value="'.$i.'">'.$i.'</option>';
                        }
                        ?>
                      </select>
                      <span>回</span>
                    </div>
                    
                    <?php
                    }
                  }else{//新規登録の場合(回数)
                  ?>
                  <div id="rep-form" class="input-traning">
                    <input type="number" name="rep[]" step ="1" min="0" class="input-weight rep">
                    <select>
                      <option value="" >選択</option>
                      <?php 
                      for($i = 1; $i <= 100; $i++){
                        echo '<option value="'.$i.'">'.$i.'</option>';
                      }
                      ?>
                    </select>
                    <span>回</span>
                  </div>
                  <?php
                  }
                  ?>
                </label>
              </div>
              <div class="weight-rep">
                <label class="<?php if(!empty($err_msg['t_weight'])) echo 'err'; ?>">
                  <div class="input-msg">
                    <span class="input-title">重量</span>
                  </div>
                  
                  <?php
                  if(!empty($_POST)){//バリデーションで引っかかった場合追加したフォームと値を残す処理(重量)
                    foreach($t_weight as $key=>$val){
                    $key .= 't_weight';
                  ?>
                  <div id="weight-form"  class="input-traning <?php if(!empty($err_msg[$key])) echo 'err'; ?>">
                    <div class="add-area-msg">
                    <?php
                    echo getErrMsg($key);
                    ?>
                    </div>
                    <input type="number" name="t_weight[]" step ="0.1" min="0" class="input-weight t-weight" value="<?php echo sanitize($val); ?>">
                    <select>
                      <option value="" >選択</option>
                      <?php 
                      for($i = 0; $i <= 500; $i += 2.5){
                        echo '<option value="'.$i.'">'.$i.'</option>';
                      }
                      ?>
                    </select>
                    <span>kg</span>
                  </div>
                  <?php
                  }
                  ?>
                  
                  <?php
                  }elseif($edit_flg){//編集画面したいトレーニングのフォーム数と値を表示する処理(重量)
                    foreach(unserialize($dbTraningData['t_weight']) as $val3){
                  ?>
                    <div id="weight-form"  class="input-traning">
                      <input type="number" name="t_weight[]" step ="0.1" min="0" class="input-weight t-weight" value="<?php echo sanitize($val3); ?>">
                      <select>
                        <option value="" >選択</option>
                        <?php 
                        for($i = 0; $i <= 500; $i += 2.5){
                          echo '<option value="'.$i.'">'.$i.'</option>';
                        }
                        ?>
                      </select>
                      <span>kg</span>
                    </div>
                    
                  <?php
                    }
                  }else{//新規登録の場合(重量)
                  ?>
                  <div id="weight-form"  class="input-traning">
                    <input type="number" name="t_weight[]" step ="0.1" min="0" class="input-weight t-weight">
                    <select>
                      <option value="" >選択</option>
                      <?php 
                      for($i = 0; $i <= 500; $i += 2.5){
                        echo '<option value="'.$i.'">'.$i.'</option>';
                      }
                      ?>
                    </select>
                    <span>kg</span>
                  </div>
                  <?php
                  }
                  ?>
                </label>
              </div>
            </div>
            <?php
            if($edit_flg){
            ?>
            <a href="mypage.php<?php echo appendGetParam(array('t_id')); ?>" class="back">&lt; 一覧に戻る</a>
            <?php
            }
            ?>
          </div>
          <div class="form-fieldset">
            <input type="submit" class="btn" value="<?php echo (!$edit_flg) ? '登録する' : '編集する'; ?>">
          </div>
        </div>
      </div>
    </section>
  
    <!-- サイドバー -->
    <?php
    require('sidebar.php');
    ?>
    </div>
  </form>
  
   <!-- footer -->
  <?php
  require('footer.php');
  ?>