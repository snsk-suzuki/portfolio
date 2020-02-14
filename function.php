<?php
//error_reporting(E_ALL); //E_STRICTレベル以外のエラーを報告する
//ini_set('display_errors','On'); //画面にエラーを表示させるか
//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = false;
//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}

//================================
// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと３０日削除されない）
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（３０日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60*60*24*30);
//セッション名変更
session_name("fitness");
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug('ログイン期限日時タイムスタンプ：'.($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'エラーが発生しました。しばらく経ってからやり直してください');
define('MSG04', 'そのEmailは既に登録されています');
define('MSG05', '255文字以内で入力してください');
define('MSG06', '6文字以上で入力してください');
define('MSG07', '半角英数字のみご利用頂けます');
define('MSG08', 'パスワード（再入力）が合っていません');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '半角数字のみご利用いただけます');
define('MSG11', '小数点1桁までの数字で入力してください');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('SUC01', 'プロフィールを変更しました');
define('SUC02', 'パスワードを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '記録しました');
define('SUC05', '削除しました');
define('SUC06', '削除するデータがありません');

//エラーメッセージ格納用の配列
$err_msg = array();

//================================
// バリデーション関数
//================================

//エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}
//未入力チェック
function validRequired($str, $key){
  if($str === ''){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
//Email形式チェック
function validEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
//Email重複チェック
function validEmailDup($email){
  global $err_msg;
  //例外処理
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG04;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG03;
  }
}
//最大文字数チェック
function validMaxLen($str, $key, $max = 256) {
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
//最小文字数チェック
function validMinLen($str, $key, $min = 6) {
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}
//半角英数字チェック
function validHalf($str, $key) {
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG07;
  }
}
//同値チェック
function validMatch($str1, $str2, $key) {
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG08;
  }
}
//半角数字チェック
function validNumber($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
//パスワードチェック
function validPass($str, $key){
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}
//固定長チェック
function validLength($str, $key, $len = 8){
  if( mb_strlen($str) !== $len ){
    global $err_msg;
    $err_msg[$key] = $len . MSG14;
  }
}
//================================
// データベース
//================================
//DB接続関数
function dbConnect(){
  //DBへの接続準備
  $db = parse_url($_SERVER['CLEARDB_DATABASE_URL']);
  $db['dbname'] = ltrim($db['path'], '/');
  $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8";
  $user = $db['user'];
  $password = $db['pass'];
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    //PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn,$user,$password,$options);
  return $dbh;
}
//SQL実行関数
function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました');
    debug('失敗したSQL：'.print_r($stmt, true));
    global $err_msg;
    $err_msg['common'] = MSG03;
    return 0;
  }
  debug('クエリ成功');
  return $stmt;
}
function getUser($u_id){
  debug('ユーザー情報を取得します');
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT id, username, age, height, weight, fat, email, password, login_time, pic, delete_flg, create_date, updatedate FROM users WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果のデータを１レコード返却
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
    
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}
function getTraning($u_id, $t_id){
  debug('トレーニング情報を取得します。');
  debug('ユーザーID：'.$u_id);
  debug('トレーニングID：'.$t_id);
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT id, traning, rep, t_weight, user_id, create_date, update_date FROM traningedit WHERE user_id = :u_id AND id = :t_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':t_id' => $t_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果のデータを１レコード返却
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
    
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}
function getEvent($u_id){
  debug('カテゴリー情報を取得します。');
  debug('ユーザーID：'.$u_id);
  //例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT DISTINCT traning FROM traningedit WHERE user_id = :u_id';//値が重複したレコードはひとつにする
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果のデータを全レコード返却
    if($stmt){
      return $stmt->fetchAll();
  }else{
      return false;
    }
  
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}
function getTraningList($u_id, $currentMinNum = 1, $event, $sort, $span = 10){
  debug('トレーニング情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM traningedit WHERE user_id = :u_id AND delete_flg = 0';
    if(!empty($event)){//種目のソート
      $sql .= ' AND traning = :event';
    }
    if(!empty($sort)){//日付のソート
      if($sort = 2){
        $sql .= ' ORDER BY create_date ASC';
      }else{
        $sql .= ' ORDER BY create_date DESC';
      }
    }else{
      $sql .= ' ORDER BY create_date DESC';
    }
    if(!empty($event)){
      $data = array(':u_id' => $u_id, ':event' => $event);
    }else{
      $data = array(':u_id' => $u_id,);
    }
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    if(!$stmt){
      return false;
    }
    
    // ページング用のSQL文作成
    $sql = 'SELECT id, traning, rep, t_weight, create_date FROM traningedit WHERE user_id = :u_id AND delete_flg = 0';
    if(!empty($event)){
      $sql .= ' AND traning = :event';
    }
    if(!empty($sort)){
      if($sort = 2){
        $sql .= ' ORDER BY create_date ASC';
      }else{
        $sql .= ' ORDER BY create_date DESC';
      }
    }else{
      $sql .= ' ORDER BY create_date DESC';
    }
    $sql .= ' LIMIT :span OFFSET :currentMinNum';
    debug('SQL：'.$sql);
    //クエリー作成
    $stmt = $dbh->prepare($sql);
    //プレースホルダに値をセット
    $stmt->bindValue(':u_id', $u_id, PDO::PARAM_STR);
    if(!empty($event)) $stmt->bindValue(':event', $event, PDO::PARAM_STR);
    $stmt->bindValue(':span', $span, PDO::PARAM_INT);
    $stmt->bindValue(':currentMinNum', $currentMinNum, PDO::PARAM_INT);
    //SQL文を実行
    if(!$stmt->execute()){
      debug('クエリに失敗しました');
      debug('失敗したSQL：'.print_r($stmt, true));
      global $err_msg;
      $err_msg['common'] = MSG03;
      return false;
    }else{
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }
    
    
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}
//================================
// メール送信
//================================
function sendMail($from, $to, $subject, $comment){
  if(!empty($to) && !empty($subject) && !empty($comment)){
    //文字化けしないように設定（お決まりパターン）
    mb_language("Japanese"); //現在使っている言語を設定する
    mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定
    
    //メールを送信（送信結果はtrueかfalseで返ってくる）
    $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
    //送信結果を判定
    if ($result) {
      debug('メールを送信しました。');
    } else {
      debug('【エラー発生】メールの送信に失敗しました。');
    }
  }
}
//================================
// その他
//================================
//サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}
//フォーム入力保持
function getFormData($str, $flg = false){
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }
  global $dbFormData;
  global $err_msg;
  //ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      //POSTにデータがある場合
      if(isset($method[$str])){
        return sanitize($method[$str]);
      }else{
        //ない場合（フォームにエラーがある＝POSTされてるはずなので、まずあり得ないが）はDB表示
        return sanitize($dbFormData[$str]);
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合（このフォームを変更していてエラーはないが、他のフォームで引っかかっている状態）
      if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
        return sanitize($method[$str]);
      }else{//そもそも変更してない
        return sanitize($dbFormData[$str]);
      }
    }
  }else{
    if(isset($method[$str])){
      return sanitize($method[$str]);
    }
  }
}
//sessionを1回だけ取得できる
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}
//画像処理
function uploadImg($file, $key){
  debug('画像アップロード情報');
  debug('FILE情報：'.print_r($file,true));
  
  if(isset($file['error']) && is_int($file['error'])){
    try {
      switch ($file['error']){
        case UPLOAD_ERR_OK: //OK
        break;
        case UPLOAD_ERR_NO_FILE: //ファイル未選択の場合
        throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE: // php.ini定義の最大サイズが超過した場合
        throw new RuntimeException('ファイルサイズが大きすぎます');
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
        throw new RuntimeException('ファイルサイズが大きすぎます');
        default: // その他の場合
        throw new RuntimeException('その他のエラーが発生しました');
      }
      
      //MIMEタイプのチェック
      $type = @exif_imagetype($file['tmp_name']);
      if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        throw new RuntimeException('画像形式が未対応です');
      }
      
      //同じファイル名で保存されないようハッシュ化して拡張子を取得して保存する
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if(!move_uploaded_file($file['tmp_name'], $path)){
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);
      
      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;
    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
    
  }
}
//認証キー生成
function makeRandKey($length = 8) {
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; ++$i) {
      $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}
//画像表示用関数
function showImg($path){
  if(empty($path)){
    return 'img/sample-img.png';
  }else{
    return $path;
  }
}
//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
  $minPageNum = $currentPageNum - 4;
  $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
  $minPageNum = $currentPageNum - 3;
  $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum >= $pageColNum){
  $minPageNum = $currentPageNum - 1;
  $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum >= $pageColNum){
  $minPageNum = $currentPageNum;
  $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
  $minPageNum = 1;
  $maxPageNum = $totalPageNum;
  // それ以外は左右に２個出す。
  }else{
  $minPageNum = $currentPageNum - 2;
  $maxPageNum = $currentPageNum + 2;
  }
  
  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $totalPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$totalPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}
//GETパラメータ付与
// $arr_del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}