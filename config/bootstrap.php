<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

declare(strict_types=1);

use yii\bootstrap5\ActiveField;

define('K_PATH_FONTS', dirname(__DIR__) . '/data/fonts/_tcpdf/');

if (!YII_ENV_PROD) {
    ini_set('zend.assertions', '-1');
    ini_set('assert.exception', '1');
}

Yii::$container->set(ActiveField::class, [
    'errorOptions' => [
        'class' => 'invalid-feedback',
    ],
    'hintOptions' => [
        'class' => [
            'widget' => 'form-text',
            'text-muted',
        ],
        'tag' => 'div',
    ],
    'options' => [
        'class' => [
            'widget' => 'form-group mb-3',
        ],
    ],
]);
