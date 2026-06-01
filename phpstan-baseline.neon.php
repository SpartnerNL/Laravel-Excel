<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;

$includes = [];

$isPsrV3 = InstalledVersions::satisfies(new VersionParser, 'psr/simple-cache', '^3.0');

if (!$isPsrV3) {
    // psr/simple-cache ^1.0 or ^2.0: exclude typed cache classes that require v3 interface.
    $includes[] = __DIR__ . '/phpstan-baseline-psr-cache-pre-3.neon';
} else {
    // psr/simple-cache ^3.0: exclude untyped deprecated cache classes.
    $includes[] = __DIR__ . '/phpstan-baseline-psr-cache-3.neon';
}

$hasQueueAttributes = InstalledVersions::isInstalled('laravel/framework')
    && InstalledVersions::satisfies(new VersionParser, 'laravel/framework', '>=13.0');

if (!$hasQueueAttributes) {
    // Laravel < 13: the #[Queue]/#[Connection] attributes don't exist yet, so
    // QueueResolver's attribute path is unresolvable to PHPStan (it falls back
    // to a property read at runtime via a trait_exists guard).
    $includes[] = __DIR__ . '/phpstan-baseline-queue-attributes-pre-13.neon';
}

return ['includes' => $includes];
