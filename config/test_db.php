<?php

declare(strict_types=1);

return (function (): array {
    $db = require(__DIR__ . '/db.php');

    $path = dirname(__DIR__) . '/database/test-db.sqlite';
    $db['dsn'] = 'sqlite:' . $path;

    return $db;
})();
