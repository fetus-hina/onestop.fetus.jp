<?php

declare(strict_types=1);

namespace app\assets;

use yii\bootstrap4\BootstrapAsset;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
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
        FontAwesomeAsset::class,
        YiiAsset::class,
    ];
}
