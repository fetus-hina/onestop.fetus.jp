<?php

declare(strict_types=1);

use app\assets\AppAsset;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 * @var string $content
 */

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
    <header class="mb-3">
      <div class="container">
        <h1><a href="https://fetus.jp/">fetus</a></h1>
      </div>
    </header>
    <div class="container">
      <h1>onestop.fetus.jp</h1>
    </div>
    <hr>
    <div class="container">
      <?= $content . "\n" ?>
    </div>
    <footer>
      <hr>
      <div class="container text-end pb-3">
        <?= implode('<br>', array_filter([
          vsprintf('Copyright &copy; 2017-%d %s %s', [
            (int)$now->format('Y'),
            Html::a(
              Html::encode('AIZAWA Hina'),
              'https://fetus.jp/'
            ),
            implode(' ', [
              Html::a(
                Html::tag('span', '', ['class' => 'fab fa-twitter']),
                'https://twitter.com/fetus_hina'
              ),
              Html::a(
                Html::tag('span', '', ['class' => 'fab fa-github']),
                'https://github.com/fetus-hina'
              ),
            ]),
          ]),
          Yii::$app->params['revision']
            ? implode(', ', array_filter([
              Yii::$app->params['revision']['version']
                ? vsprintf('Version %s', [
                  Html::a(
                    Html::encode(Yii::$app->params['revision']['version']),
                    vsprintf('https://github.com/fetus-hina/onestop.fetus.jp/releases/tag/%s', [
                      rawurlencode(Yii::$app->params['revision']['version']),
                    ])
                  ),
                ])
                : null,
              vsprintf('Revision %s', [
                Html::a(
                  Html::encode(Yii::$app->params['revision']['short']),
                  vsprintf('https://github.com/fetus-hina/onestop.fetus.jp/tree/%s', [
                    rawurlencode(Yii::$app->params['revision']['hash']),
                  ])
                ),
              ]),
            ]))
            : null,
          vsprintf('Powered by %s', [
            preg_replace(
              '/,(?=[^,]+$)/', // 最後のカンマ
              ' and ',
              implode(', ', [
                Html::a(
                  Html::encode('Yii Framework'),
                  'https://www.yiiframework.com/',
                ),
                Html::a(
                  Html::encode('TCPDF'),
                  'https://tcpdf.org/',
                ),
              ])
            ),
          ]),
        ])) . "\n" ?>
      </div>
    </footer>
<?php $this->endBody(); echo "\n"; ?>
  </body>
</html>
<?php $this->endPage() ?>
