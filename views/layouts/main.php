<?php

declare(strict_types=1);

use app\assets\AppAsset;
use app\helpers\Icon;
use app\helpers\TypeHelper;
use yii\base\Application;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var string $content
 */

$app = TypeHelper::instanceOf(Yii::$app, Application::class);
$revision = is_array($app->params['revision'] ?? null) ? $app->params['revision'] : [];
$revisionVersion = is_string($revision['version'] ?? null) ? $revision['version'] : '';
$revisionShort = is_string($revision['short'] ?? null) ? $revision['short'] : '';
$revisionHash = is_string($revision['hash'] ?? null) ? $revision['hash'] : '';

AppAsset::register($this);

$this->registerLinkTag([
  'href' => Url::to('@web/favicon/favicon.svg'),
  'rel' => 'icon',
  'sizes' => 'any',
  'type' => 'image/svg+xml',
]);

foreach ([57, 60, 72, 76, 114, 120, 144, 152, 180] as $size) {
  $this->registerLinkTag([
    'href' => Url::to("@web/favicon/apple-touch-icon-{$size}.png"),
    'rel' => 'apple-touch-icon',
    'sizes' => "{$size}x{$size}",
    'type' => 'image/png',
  ]);
}

$this->registerMetaTag([
  'name' => 'viewport',
  'content' => 'width=device-width,initial-scale=1',
]);

$now = new DateTimeImmutable('now', new DateTimeZone($app->timeZone));

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<?= Html::beginTag('html', [
  'class' => 'h-100',
  'lang' => $app->language,
]) . "\n" ?>
  <head>
    <?= Html::tag('meta', '', ['charset' => $app->charset]) . "\n" ?>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head(); echo "\n"; ?>
  </head>
  <?= Html::beginTag('body', [
    'class' => [
      'back-to-top-auto',
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
          $revisionShort !== ''
            ? implode(', ', array_filter([
              $revisionVersion !== ''
                ? vsprintf('Version %s', [
                  Html::a(
                    Html::encode($revisionVersion),
                    vsprintf('https://github.com/fetus-hina/onestop.fetus.jp/releases/tag/%s', [
                      rawurlencode($revisionVersion),
                    ])
                  ),
                ])
                : null,
              vsprintf('Revision %s', [
                Html::a(
                  Html::encode($revisionShort),
                  vsprintf('https://github.com/fetus-hina/onestop.fetus.jp/tree/%s', [
                    rawurlencode($revisionHash),
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
