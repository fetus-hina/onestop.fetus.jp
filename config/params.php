<?php

declare(strict_types=1);

return (fn (): array => [
    'adminEmail' => 'admin@example.com',
    'revision' => file_exists(__DIR__ . '/git-revision.php')
            ? include(__DIR__ . '/git-revision.php')
            : null,
])();
