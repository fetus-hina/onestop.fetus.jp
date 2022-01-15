<?php

declare(strict_types=1);

use app\models\User;
use yii\caching\FileCache;
use yii\log\FileTarget;

return (function (): array {
    $params = require(__DIR__ . '/params.php');
    $config = [
        'id' => 'onestop-fetus-jp',
        'name' => 'onestop.fetus.jp',
        'basePath' => dirname(__DIR__),
        'sourceLanguage' => 'en-US',
        'language' => 'ja-JP',
        'charset' => 'UTF-8',
        'timeZone' => 'Asia/Tokyo',
        'bootstrap' => ['log'],
        'aliases' => [
            '@node' => '@app/node_modules',
            '@bower' => '@node',
            '@npm' => '@node',
        ],
        'components' => [
            'assetManager' => require(__DIR__ . '/web/asset-manager.php'),
            'request' => [
                'cookieValidationKey' => require(__DIR__ . '/cookie-secret.php'),
            ],
            'cache' => [
                'class' => FileCache::class,
            ],
            'user' => [
                'identityClass' => User::class,
                'enableAutoLogin' => false,
            ],
            'errorHandler' => [
                'errorAction' => 'site/error',
            ],
            'log' => [
                'traceLevel' => defined('YII_DEBUG') && YII_DEBUG ? 3 : 0,
                'targets' => [
                    [
                        'class' => FileTarget::class,
                        'levels' => ['error', 'warning'],
                    ],
                ],
            ],
            'db' => require(__DIR__ . '/db.php'),
            'urlManager' => [
                'enablePrettyUrl' => true,
                'showScriptName' => false,
                'rules' => [
                    '' => 'site/index',
                    '<action:\w+>' => 'site/<action>',
                ],
            ],
        ],
        'params' => $params,
    ];

    return $config;
})();
