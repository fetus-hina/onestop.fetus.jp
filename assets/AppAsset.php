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
    public $sourcePath = '@app/resources';
    public $css = [
        'css/site.css',
    ];
    public $js = [
        'js/fakedata.js',
        'js/mynumber.js',
        'js/zipsearch.js',
    ];
    public $depends = [
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        JqueryAsset::class,
        YiiAsset::class,
    ];
}
