<?php

declare(strict_types=1);

use yii\db\Connection;

return (function (): array {
    $path = dirname(__DIR__) . '/database/db.sqlite';

    return [
        'class' => Connection::class,
        'dsn' => 'sqlite:' . $path,
    ];
})();
