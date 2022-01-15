<?php

declare(strict_types=1);

namespace app\helpers;

use LogicException;
use TypeError;
use Yii;
use app\assets\BootstrapIconsAsset;
use yii\helpers\Html;
use yii\web\AssetBundle;
use yii\web\View;

final class Icon
{
    // check
    // dismiss
    // download
    // filePdf
    // github
    // help
    // twitter

    public static function check(): string
    {
        return self::renderIcon(IconSource::BOOTSTRAP_ICONS, 'bi-check-lg');
    }

    public static function dismiss(): string
    {
        return self::renderIcon(IconSource::BOOTSTRAP_ICONS, 'bi-x-lg');
    }

    public static function download(): string
    {
        return self::renderIcon(IconSource::BOOTSTRAP_ICONS, 'bi-download');
    }

    public static function filePdf(): string
    {
        return self::renderIcon(IconSource::BOOTSTRAP_ICONS, 'bi-file-earmark-pdf-fill');
    }

    public static function github(): string
    {
        return self::renderIcon(IconSource::BOOTSTRAP_ICONS, 'bi-github');
    }

    public static function help(): string
    {
        return self::renderIcon(IconSource::BOOTSTRAP_ICONS, 'bi-question-circle');
    }

    public static function twitter(): string
    {
        return self::renderIcon(IconSource::BOOTSTRAP_ICONS, 'bi-twitter');
    }

    private static function renderIcon(string $source, mixed $data): string
    {
        self::registerAsset($source);
        return self::renderIconImpl($source, $data);
    }

    private static function registerAsset(string $source): AssetBundle
    {
        $view = Yii::$app->view;
        if (!$view instanceof View) {
            throw new LogicException();
        }

        return match ($source) {
            IconSource::BOOTSTRAP_ICONS => BootstrapIconsAsset::register($view),
            default => throw new LogicException(),
        };
    }

    private static function renderIconImpl(string $source, mixed $data): string
    {
        return match ($source) {
            IconSource::BOOTSTRAP_ICONS => self::renderBootstrapIcon($data),
            default => throw new LogicException(),
        };
    }

    private static function renderBootstrapIcon(mixed $class): string
    {
        if (!is_string($class)) {
            throw new TypeError();
        }

        return Html::tag('span', '', [
            'class' => ['bi', $class],
        ]);
    }
}