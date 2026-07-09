<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Arr;

trait MapsCsvSettings
{
    protected static ?string $delimiter = ',';

    protected static string $enclosure = '"';

    protected static string $lineEnding = PHP_EOL;

    protected static bool $useBom = false;

    protected static bool $includeSeparatorLine = false;

    protected static bool $excelCompatibility = false;

    protected static string $escapeCharacter = '\\';

    protected static bool $contiguous = false;

    protected static string $inputEncoding = 'UTF-8';

    protected static string $outputEncoding = '';

    protected static bool $testAutoDetect = true;

    /**
     * @param  array<string, mixed>  $config
     */
    public static function applyCsvSettings(array $config): void
    {
        static::$delimiter            = Arr::get($config, 'delimiter', static::$delimiter);
        static::$enclosure            = Arr::get($config, 'enclosure', static::$enclosure);
        static::$lineEnding           = Arr::get($config, 'line_ending', static::$lineEnding);
        static::$useBom               = Arr::get($config, 'use_bom', static::$useBom);
        static::$includeSeparatorLine = Arr::get($config, 'include_separator_line', static::$includeSeparatorLine);
        static::$excelCompatibility   = Arr::get($config, 'excel_compatibility', static::$excelCompatibility);
        static::$escapeCharacter      = Arr::get($config, 'escape_character', static::$escapeCharacter);
        static::$contiguous           = Arr::get($config, 'contiguous', static::$contiguous);
        static::$inputEncoding        = Arr::get($config, 'input_encoding', static::$inputEncoding);
        static::$outputEncoding       = Arr::get($config, 'output_encoding', static::$outputEncoding);
        static::$testAutoDetect       = Arr::get($config, 'test_auto_detect', static::$testAutoDetect);
    }
}
