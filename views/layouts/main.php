<?php

declare(strict_types=1);

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
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
    <div class="container-fluid bg-dark mb-1">
      <nav class="navbar navbar-dark bg-faded">
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
        Copyright &copy; 2017-2020 AIZAWA Hina
        <a href="https://twitter.com/fetus_hina" target="_blank"><span class="fab fa-twitter"></span></a>
        <a href="https://github.com/fetus-hina" target="_blank"><span class="fab fa-github"></span></a><br>
        Powered by <a href="http://www.yiiframework.com/" target="_blank">Yii Framework</a>
      </div>
    </footer>
<?php $this->endBody(); echo "\n"; ?>
  </body>
</html>
<?php $this->endPage() ?>
