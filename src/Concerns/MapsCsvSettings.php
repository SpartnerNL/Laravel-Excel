<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Arr;

trait MapsCsvSettings
{
    /**
     * @var string
     */
    protected static $delimiter = ',';

    /**
     * @var string
     */
    protected static $enclosure = '"';

    /**
     * @var string
     */
    protected static $lineEnding = PHP_EOL;

    /**
     * @var bool
     */
    protected static $useBom = false;

    /**
     * @var bool
     */
    protected static $includeSeparatorLine = false;

    /**
     * @var bool
     */
    protected static $excelCompatibility = false;

    /**
     * @var string
     */
    protected static $escapeCharacter = '\\';

    /**
     * @var bool
     */
    protected static $contiguous = false;

    /**
     * @var string
     */
    protected static $inputEncoding = 'UTF-8';

    /**
     * @var string
     */
    protected static $outputEncoding = '';

    /**
     * @param  array  $config
     */
    public static function applyCsvSettings(array $config)
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
    }
}
