<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="ui inverted menu">
  <div class="ui container">
    
    <a class="header item">
      Portfolio Project
    </a>
    
    <a href="index.php" class="item <?= $currentPage == 'index.php' ? 'active' : '' ?>">Home</a>
    <a href="apitester.php" class="item <?= $currentPage == 'apitester.php' ? 'active' : '' ?>">APIs</a>
    <a href="aboutpage.php" class="item <?= $currentPage == 'aboutpage.php' ? 'active' : '' ?>">About</a>
  </div>
</div>
