<?php

declare(strict_types=1);

$random = base64_encode(random_bytes(24));
$random = rtrim($random, '=');
$random = strtr($random, '+/', '-_');

$template = trim((string)file_get_contents(__FILE__, false, null, __COMPILER_HALT_OFFSET__));

echo str_replace('{random}', addslashes($random), $template) . "\n";

// phpcs:disable

__halt_compiler();
<?php

declare(strict_types=1);

return '{random}';
