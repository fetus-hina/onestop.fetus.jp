<?php

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
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'js' => [ 'jquery.min.js' ],
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [ 'css/bootstrap.min.css' ],
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js' => [ 'js/bootstrap.min.js' ],
                ],
                'yii\bootstrap\TetherAsset' => [
                    'css' => [ 'css/tether.min.css' ],
                    'js' => [ 'js/tether.min.js' ],
                ],
            ],
        ],
        'request' => [
            'cookieValidationKey' => require(__DIR__ . '/cookie-secret.php'),
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        // 'i18n' => [
        //     'translations' => [
        //         'app*' => [
        //             'class' => 'yii\i18n\PhpMessageSource',
        //             'fileMap' => [
        //             ],
        //         ],
        //     ],
        // ],
    ],
    'params' => $params,
];

return $config;
