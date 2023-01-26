<?php

declare(strict_types=1);

namespace app\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\YiiAsset;

final class AppAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@app/resources';

    /**
     * @var string[]
     */
    public $js = [
        'js/fakedata.js',
        'js/mynumber.js',
        'js/zipsearch.js',
    ];

    /**
     * @var string[]
     */
    public $depends = [
        BackToTopAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        JqueryAsset::class,
        YiiAsset::class,
    ];
}
