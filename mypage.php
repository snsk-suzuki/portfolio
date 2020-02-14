<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// GETパラメータを取得
//--------------------------------
// カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページ目
//種目
$event = (!empty($_GET['event'])) ? $_GET['event'] : '';
//ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
// 表示件数
$listSpan = 10;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1-1)*20 = 0 、 ２ページ目なら(2-1)*20 = 20
//DBからユーザーデータを取得
$dbUserData =getUser($_SESSION['user_id']);
debug('カテゴリ'.print_r($dbUserData, true));
//DBから種目データを取得
$dbEventData = getEvent($_SESSION['user_id']);
debug('種目'.print_r($dbEventData, true));
//DBからトレーニングデータを取得
$dbTraningData = getTraningList($_SESSION['user_id'], $currentMinNum, $event, $sort);
debug('トレーニング'.print_r($dbTraningData, true));

?>

<?php
$siteTitle = 'マイページ';
require ('head.php');
?>

<body class="page-3colum">
  
 <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  
  <p id="js-show-msg" style="display:none" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
    
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
  <!--メイン-->
    <section id="main">
      <div class="search-title">
        <div class="search-left">
          <span class="total-num"><?php echo sanitize($dbTraningData['total']); ?></span>
          件見つかりました
        </div>
        <div class="search-right">
          <span class="num"><?php echo (!empty($dbTraningData['data'])) ? $currentMinNum+1 : 0; ?></span>
          -
          <span class="num"><?php echo $currentMinNum+count($dbTraningData['data']); ?> 件</span>
          /
          <span class="num"><?php echo sanitize($dbTraningData['total']); ?>件中</span>
        </div>
      </div>
      <?php
      if(!empty($dbTraningData['data'])){
        foreach($dbTraningData['data'] as $val){
      ?>
      <div class="panel">
        <div class="date"><?php echo sanitize($val['create_date']); ?></div>
        <div class="event-record"><?php echo sanitize($val['traning']); ?></div>
        <ul type="circle" class="set-record">
          <?php
          $i = 1;
          foreach(array_map(null, unserialize($val['t_weight']), unserialize($val['rep'])) as $val1){
          ?>
          <li>セット<?php echo $i; ?>：<?php echo sanitize($val1[0]); ?>kg×<?php echo sanitize($val1[1]); ?>回</li>
          <?php
          $i++;
          }
          ?>
        </ul>
        <div class="editDelete">
          <a href="mytraning.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&t_id='.$val['id'] : '?t_id='.$val['id']; ?>" class="btn edit">編集</a>
          <a href="delete.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&t_id='.$val['id'] : '?t_id='.$val['id']; ?> " class="btn delete">削除</a>
        </div>
      </div>
        <?php
        }
        ?>
      <?php
      }
      pagination($currentPageNum, $dbTraningData['total_page'], '&event='.$event.'&sort='.$sort);
      ?>
    </section>
    
  <!-- サイドバー -->
    <section id="sidebar">
      <form name="" method="get">
        <p class="title">種目</p>
        <div class="selectbox">
          <select name="event">
            <option value="0" <?php if(getFormData('event',true) == 0 ){ echo 'selected'; } ?> >選択してください</option>
            <?php
              foreach($dbEventData as $val){
            ?>
            <option value="<?php if(!empty($dbEventData)) echo sanitize($val['traning']) ?>" <?php if(getFormData('event',true) == $val['traning'] ){ echo 'selected'; } ?> >
               <?php if(!empty($dbEventData)) echo sanitize($val['traning']); ?>
            </option>
            <?php
              }
            ?>
          </select>
        </div>
        <p class="title">日付順</p>
        <div class="selectbox">
          <select name="sort">
            <option value="0" <?php if(getFormData('sort',true) == 0 ){ echo 'selected'; } ?> >選択してください</option>
            <option value="1" <?php if(getFormData('sort',true) == 1 ){ echo 'selected'; } ?> >日付が新しい順</option>
            <option value="2" <?php if(getFormData('sort',true) == 2 ){ echo 'selected'; } ?> >日付が古い順</option>
          </select>
        </div>
        <input type="submit" value="検索">
      </form>
      <a href="mytraning.php">トレーニングを記録する</a>
      <a href="profEdit.php">プロフィール編集</a>
      <a href="passEdit.php">パスワード変更</a>
      <a href="withdraw.php">退会</a>
    </section>
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>
