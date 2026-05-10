<?php

declare(strict_types=1);

return (function (): array {
    $db = require __DIR__ . '/db.php';
    if (!is_array($db)) {
        throw new TypeError();
    }

    $path = dirname(__DIR__) . '/database/test-db.sqlite';
    $db['dsn'] = 'sqlite:' . $path;

    return $db;
})();
