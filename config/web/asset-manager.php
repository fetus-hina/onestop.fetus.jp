<?php

declare(strict_types=1);

use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\web\AssetManager;
use yii\web\JqueryAsset;

return [
    'class' => AssetManager::class,
    'bundles' => [
        BootstrapAsset::class => [
            'sourcePath' => '@node/@fetus-hina/fetus.css/dist',
            'css' => [
                'bootstrap.min.css',
            ],
        ],
        BootstrapPluginAsset::class => [
            'js' => [
                'js/bootstrap.bundle.min.js',
            ],
        ],
        JqueryAsset::class => [
            'js' => [
                'jquery.min.js',
            ],
        ],
    ],
];
