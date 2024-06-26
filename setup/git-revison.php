<?php //phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

declare(strict_types=1);

$data = getCurrentRevision();
$data['{version}'] = getCurrentVersion($data['{hash}']);

$template = trim((string)file_get_contents(__FILE__, false, null, __COMPILER_HALT_OFFSET__));
echo str_replace(array_keys($data), array_values($data), $template) . "\n";

function getCurrentRevision(): array
{
    $cmdline = vsprintf('/usr/bin/env %s log -n 1 --pretty=%s', [
        escapeshellarg('git'),
        escapeshellarg('format:%h:%H'),
    ]);
    exec($cmdline, $lines, $status);
    if ($status !== 0) {
        exit(1);
    }

    $hashes = explode(':', trim($lines[0]));
    if (
        preg_match('/^[0-9a-f]{7,}$/', $hashes[0]) &&
        preg_match('/^[0-9a-f]{40}$/', $hashes[1])
    ) {
        return [
            '{hash}' => $hashes[1],
            '{short}' => $hashes[0],
        ];
    }
    exit(1);
}

function getCurrentVersion(string $fullHash): ?string
{
    if (!$versions = getVersionTags($fullHash)) {
        return null;
    }

    return $versions[0];
}

function getVersionTags(string $fullHash): array
{
    $cmdline = vsprintf('/usr/bin/env %s tag --points-at=%s', [
        escapeshellarg('git'),
        escapeshellarg($fullHash),
    ]);
    exec($cmdline, $lines, $status);
    if ($status !== 0) {
        exit(1);
    }

    $versions = array_filter(
        array_map(trim(...), $lines),
        fn (string $line): bool => (bool)preg_match('/^v([0-9.]+)$/', $line),
    );
    usort($versions, fn ($a, $b) => version_compare($b, $a));
    return array_values($versions);
}

// phpcs:disable
__halt_compiler();
<?php

declare(strict_types=1);

return [
    'hash' => '{hash}',
    'short' => '{short}',
    'version' => '{version}',
];
