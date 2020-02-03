<?php

declare(strict_types=1);

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);

$now = new DateTimeImmutable('now', new DateTimeZone(Yii::$app->timeZone));
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<?= Html::beginTag('html', ['lang' => Yii::$app->language]) . "\n" ?>
  <head>
    <?= Html::tag('meta', '', ['charset' => Yii::$app->charset]) . "\n" ?>
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
          <a class="navbar-brand" href="/"><?= Html::encode(Yii::$app->name) ?></a>
        </div>
      </nav>
    </div>
    <div class="container">
      <?= $content . "\n" ?>
    </div>
    <footer class="footer">
      <div class="container text-right"><?= implode('<br>', [
        vsprintf('Copyright &copy; 2017-%d AIZAWA Hina %s.', [
          (int)$now->format('Y'),
          implode(' ', [
            Html::a(
              Html::tag('span', '', ['class' => 'fab fa-twitter']),
              'https://twitter.com/fetus_hina',
              ['target' => '_blank']
            ),
            Html::a(
              Html::tag('span', '', ['class' => 'fab fa-github']),
              'https://github.com/fetus-hina',
              ['target' => '_blank']
            ),
          ]),
        ]),
        vsprintf('Powered by %s.', implode(', ', [
          Html::a(
            Html::encode('Yii Framework'),
            'https://www.yiiframework.com/',
            ['target' => '_blank']
          ),
        ])),
      ])?></div>
    </footer>
<?php $this->endBody(); echo "\n"; ?>
  </body>
</html>
<?php $this->endPage() ?>
