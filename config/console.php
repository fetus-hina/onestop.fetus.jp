<?php

declare(strict_types=1);

use yii\caching\FileCache;
use yii\gii\Module as GiiModule;
use yii\log\FileTarget;

return (function (): array {
    $params = require __DIR__ . '/params.php';
    $db = require __DIR__ . '/db.php';
    $config = [
        'id' => 'basic-console',
        'basePath' => dirname(__DIR__),
        'bootstrap' => ['log'],
        'controllerNamespace' => 'app\commands',
        'components' => [
            'cache' => [
                'class' => FileCache::class,
            ],
            'log' => [
                'targets' => [
                    [
                        'class' => FileTarget::class,
                        'levels' => ['error', 'warning'],
                    ],
                ],
            ],
            'db' => $db,
        ],
        'params' => $params,
    ];

    if (
        YII_ENV_DEV &&
        file_exists(__DIR__ . '/../vendor/yiisoft/yii2-gii')
    ) {
        $config['bootstrap'][] = 'gii';
        $config['modules']['gii'] = [
            'class' => GiiModule::class,
        ];
    }

    return $config;
})();
