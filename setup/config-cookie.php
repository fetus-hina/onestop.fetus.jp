<?php
$random = base64_encode(random_bytes(24));
$random = rtrim($random, '=');
$random = strtr($random, '+/', '-_');
printf("<?php\nreturn '%s';\n", addslashes($random));
