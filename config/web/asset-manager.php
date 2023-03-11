<?php

declare(strict_types=1);

use ParagonIE\ConstantTime\Base32;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\helpers\ArrayHelper;
use yii\web\AssetManager;
use yii\web\JqueryAsset;

return [
    'class' => AssetManager::class,
    'bundles' => [
        BootstrapAsset::class => [
            'sourcePath' => '@node/@fetus-hina/fetus.css/dist',
            'css' => [
                'bootstrap-lineseedjp.min.css',
            ],
        ],
        BootstrapPluginAsset::class => [
            'js' => [
                'dist/js/bootstrap.bundle.min.js',
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

        static $appPath = null;
        if (!$appPath) {
            $appPath = realpath(Yii::getAlias('@app'));
        }
        if ($appPath) {
            $pathParts[] = substr(
                Base32::encodeUnpadded(
                    hash('sha256', $appPath, true),
                ),
                0,
                8,
            );
        }

        $revision = ArrayHelper::getValue(Yii::$app->params, 'revision.short', null);
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
