<header>
  <div class="site-width">
    <h1><a href="index.php">Fitness Record</a></h1>
    <nav id="top-nav">
      <ul>
      <?php
        if(empty($_SESSION['user_id'])){
          ?>
          <li><a href="signup.php" class="btn">ユーザー登録</a></li>
          <li><a href="login.php" class="btn">ログイン</a></li>
      <?php
        }else{
      ?>
          <li><a href="mypage.php" class="btn">マイページ</a></li>
          <li><a href="logout.php" class="btn">ログアウト</a></li>
      <?php
        }
      ?>
      </ul>
    </nav>
    <a class="mobile-menu" id="mobile-menu">
      <div></div>
      <div></div>
      <div></div>
    </a>
  </div>
</header>