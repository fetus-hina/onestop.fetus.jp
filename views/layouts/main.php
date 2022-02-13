<?php

declare(strict_types=1);

use app\assets\AppAsset;
use app\helpers\Icon;
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
<?= Html::beginTag('html', [
  'class' => 'h-100',
  'lang' => Yii::$app->language,
]) . "\n" ?>
  <head>
    <?= Html::tag('meta', '', ['charset' => Yii::$app->charset]) . "\n" ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link type="image/svg+xml" href="https://fetus.jp/images/favicon.svg" rel="icon" sizes="any">
    <link type="image/png" href="https://fetus.jp/images/apple-touch-icon-57.png" rel="apple-touch-icon" sizes="57x57">
    <link type="image/png" href="https://fetus.jp/images/apple-touch-icon-60.png" rel="apple-touch-icon" sizes="60x60">
    <link type="image/png" href="https://fetus.jp/images/apple-touch-icon-72.png" rel="apple-touch-icon" sizes="72x72">
    <link type="image/png" href="https://fetus.jp/images/apple-touch-icon-76.png" rel="apple-touch-icon" sizes="76x76">
    <link type="image/png" href="https://fetus.jp/images/apple-touch-icon-114.png" rel="apple-touch-icon" sizes="114x114">
    <link type="image/png" href="https://fetus.jp/images/apple-touch-icon-120.png" rel="apple-touch-icon" sizes="120x120">
    <link type="image/png" href="https://fetus.jp/images/apple-touch-icon-144.png" rel="apple-touch-icon" sizes="144x144">
    <link type="image/png" href="https://fetus.jp/images/apple-touch-icon-152.png" rel="apple-touch-icon" sizes="152x152">
    <link type="image/png" href="https://fetus.jp/images/apple-touch-icon-180.png" rel="apple-touch-icon" sizes="180x180">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head(); echo "\n"; ?>
  </head>
  <?= Html::beginTag('body', [
    'class' => [
      'd-flex',
      'flex-column',
      'h-100',
    ],
  ]) . "\n" ?>
<?php $this->beginBody() ?>
    <header class="mb-3">
      <div class="container">
        <h1><a href="https://fetus.jp/">fetus</a></h1>
      </div>
    </header>
    <div class="container flex-grow-1">
      <?= $this->render('//layouts/_navbar') . "\n" ?>
      <?= Html::tag('main', $content) . "\n" ?>
    </div>
    <footer>
      <div class="container">
        <?= implode('<br>', array_filter([
          vsprintf('Copyright &copy; 2017-%d %s %s', [
            (int)$now->format('Y'),
            Html::a(
              Html::encode('AIZAWA Hina'),
              'https://fetus.jp/'
            ),
            implode(' ', [
              Html::a(
                Icon::twitter(),
                'https://twitter.com/fetus_hina'
              ),
              Html::a(
                Icon::github(),
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
