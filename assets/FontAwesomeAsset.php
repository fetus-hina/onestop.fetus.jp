<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@npm/@fortawesome/fontawesome-free/js';
    public $js = [
        'all.min.js',
    ];
    public $jsOptions = [
        'data-auto-replace-svg' => 'nest',
    ];
}
