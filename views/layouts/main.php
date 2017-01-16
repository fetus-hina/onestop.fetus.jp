<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

$app = Yii::$app;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= $app->language ?>">
  <head>
    <meta charset="<?= $app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head(); echo "\n"; ?>
  </head>
  <body>
<?php $this->beginBody() ?>
    <div class="container-fluid mb-1">
      <nav class="navbar navbar-light bg-faded">
        <div class="container">
          <a class="navbar-brand" href="/"><?= Html::encode($app->name) ?></a>
        </div>
      </nav>
    </div>
    <div class="container">
      <?= $content . "\n" ?>
    </div>
    <footer class="footer">
      <div class="container">
         Created by AIZAWA Hina.
         <a href="https://twitter.com/fetus_hina" target="_blank"><span class="fa fa-twitter"></span></a>
         <a href="https://github.com/fetus-hina" target="_blank"><span class="fa fa-github"></span></a><br>
         <?= Yii::powered() . "\n" ?>
      </div>
    </footer>
<?php $this->endBody(); echo "\n"; ?>
  </body>
</html>
<?php $this->endPage() ?>
