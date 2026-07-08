<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\MethodCall\RemoveNullArgOnNullDefaultParamRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsToSameRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictFluentReturnRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForCommonObjectDenominatorRector;
use RectorLaravel\Rector\ArrayDimFetch\EnvVariableToEnvHelperRector;
use RectorLaravel\Rector\FuncCall\AppToResolveRector;
use RectorLaravel\Rector\StaticCall\CarbonToDateFacadeRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withCache(
        cacheDirectory: '/tmp/rector',
        cacheClass: FileCacheStorage::class,
    )
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withComposerBased(
        phpunit: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
    )
    ->withImportNames()
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_120,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withSkip([
        // Skip signature-narrowing rules in src/ to preserve BC for downstream
        // subclassers. Tests can still benefit from these inferences.
        ParamTypeByMethodCallTypeRector::class => [
            __DIR__ . '/src',
        ],
        ReturnTypeFromStrictFluentReturnRector::class => [
            __DIR__ . '/src',
        ],

        // BatchCache::set/setMultiple use func_num_args() to distinguish
        // "no TTL passed" from "explicit null". Removing the explicit null
        // silently changes behavior. Scoped to the one test that depends on
        // the explicit null override.
        RemoveNullArgOnNullDefaultParamRector::class => [
            __DIR__ . '/tests/Cache/BatchCacheTest.php',
        ],

        // WithStrictNullComparisonTest round-trips mixed-type zeros through
        // xlsx; PhpSpreadsheet v5 reads them back as strings. The test's
        // intent is "not null", not exact PHP type. Keep loose comparison.
        AssertEqualsToSameRector::class => [
            __DIR__ . '/tests/Concerns/WithStrictNullComparisonTest.php',
        ],

        // `app(X::class)` → `resolve(X::class)`. Functionally equivalent but
        // `app()` is the canonical Laravel helper; reviewers and downstream
        // forks recognize it. Pure churn.
        AppToResolveRector::class,

        // `isset($_ENV['X'])` → `Env::get('X') !== null`. Not strictly
        // equivalent: Env::get parses literal "null"/"true"/"false" strings,
        // and pulls in Laravel's coercion semantics. For checks like Lambda
        // detection, the plain superglobal is the more honest expression.
        EnvVariableToEnvHelperRector::class,

        // `Carbon::now()` → `\Illuminate\Support\Facades\Date::now()`. Useful
        // when the project wants `Date::setTestNow()` mocking; for these
        // tests it's pure churn and leaves an FQN behind.
        CarbonToDateFacadeRector::class,

        // Deprecated cache drivers must keep untyped CacheInterface signatures
        // for psr/simple-cache ^1|^2 compatibility (see PR #4372). Both files
        // also carry an in-source directive to the same effect.
        __DIR__ . '/src/Cache/BatchCacheDeprecated.php',
        __DIR__ . '/src/Cache/MemoryCacheDeprecated.php',

        // Skip vendor-style fixtures or generated files if any get added.
        __DIR__ . '/tests/Data',

        // These tests declare anonymous classes that are returned from functions.
        // These identifiers are non-fixed, so declaring a return type like
        // @return \AnonymousClass8985ceefe39748fba5ed7c0d9a8fe5be[] means nothing
        AddReturnDocblockForCommonObjectDenominatorRector::class => [
            __DIR__ . '/tests/Concerns/WithMultipleSheetsTest.php',
            __DIR__ . '/tests/Concerns/WithTitleTest.php',
            __DIR__ . '/tests/Concerns/WithValidationTest.php',
            __DIR__ . '/tests/PhpSpreadsheetV5CompatibilityTest.php',
        ],
    ]);
