<?php

//================================
// ログイン認証・自動ログアウト
//================================
// ログインしている場合
if(!empty($_SESSION['login_date'])){
  debug('ログイン済みユーザーです');
  
  if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
    debug('ログイン有効期限オーバーです');
    
    //セッションを削除（ログアウトする）
    session_destroy();
    //ログインページへ
    header("Location:login.php");
  }else{
    debug('ログイン有効期限内です');
    //最終ログイン日時を更新
    $_SESSION['login_date'] = time();
    
    //mypage.phpからmypage.phpへの無限ループを回避する処理(ログイン有効期限内でlogin.phpへ遷移した場合mypage.phpへ遷移させる処理)
    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
      header("Location:mypage.php");
    }
  }
}else{
  //login.phpからlogin.phpへの無限ループを回避する処理
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
    header("Location:login.php");
  }
}