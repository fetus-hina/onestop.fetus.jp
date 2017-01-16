<?php
return (function () {
    $path = dirname(__DIR__) . '/database/db.sqlite';
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'sqlite:' . $path,
    ];
})();
