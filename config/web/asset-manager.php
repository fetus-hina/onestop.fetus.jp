<?php

declare(strict_types=1);

use ParagonIE\ConstantTime\Base32;
use app\helpers\TypeHelper;
use yii\base\Application;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\helpers\ArrayHelper;
use yii\web\AssetManager;
use yii\web\JqueryAsset;

return [
    'class' => AssetManager::class,
    'bundles' => [
        BootstrapAsset::class => [
            'sourcePath' => '@node/@jp3cki/fetus.css/dist',
            'css' => [
                'bootstrap-lineseedjp.min.css',
            ],
        ],
        BootstrapPluginAsset::class => [
            'js' => [
                'bootstrap.bundle.min.js',
            ],
        ],
        JqueryAsset::class => [
            'js' => [
                'jquery.min.js',
            ],
        ],
    ],
    'hashCallback' => function (string $path): string {
        $pathParts = [];

        /** @var string|false|null $appPath */
        static $appPath = null;
        if (!$appPath) {
            $alias = Yii::getAlias('@app');
            $appPath = is_string($alias) ? realpath($alias) : false;
        }
        if (is_string($appPath)) {
            $pathParts[] = substr(
                Base32::encodeUnpadded(
                    hash('sha256', $appPath, true),
                ),
                0,
                8,
            );
        }

        $app = TypeHelper::instanceOf(Yii::$app, Application::class);
        $revision = ArrayHelper::getValue($app->params, 'revision.short', null);
        if (is_string($revision) && preg_match('/^[0-9a-f]+$/i', $revision)) {
            $pathParts[] = strtolower($revision);
        }

        $pathParts[] = substr(
            Base32::encodeUnpadded(
                hash(
                    'sha256',
                    is_file($path) ? dirname($path) : $path,
                    true,
                ),
            ),
            0,
            8,
        );

        return implode('/', $pathParts);
    },
];
