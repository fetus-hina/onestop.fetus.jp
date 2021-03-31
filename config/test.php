<?php

declare(strict_types=1);

use app\models\User;

return (function (): array {
    $params = require(__DIR__ . '/params.php');
    $dbParams = require(__DIR__ . '/test_db.php');

    return [
        'id' => 'basic-tests',
        'basePath' => dirname(__DIR__),
        'language' => 'en-US',
        'timeZone' => 'Asia/Tokyo',
        'components' => [
            'db' => $dbParams,
            'mailer' => [
                'useFileTransport' => true,
            ],
            'urlManager' => [
                'showScriptName' => true,
            ],
            'user' => [
                'identityClass' => User::class,
            ],
            'request' => [
                'cookieValidationKey' => 'test',
                'enableCsrfValidation' => false,
            ],
        ],
        'params' => $params,
    ];
})();
