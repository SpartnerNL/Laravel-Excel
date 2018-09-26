<?php

namespace Maatwebsite\Excel\Imports;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class HeadingRowFormatter
{
    /**
     * @const string
     */
    const FORMATTER_NONE = 'none';

    /**
     * @const string
     */
    const FORMATTER_SLUG = 'slug';

    /**
     * @var string
     */
    protected static $formatter = self::FORMATTER_SLUG;

    /**
     * @var callable[]
     */
    protected static $customFormatters = [];

    /**
     * @var array
     */
    protected static $defaultFormatters = [
        self::FORMATTER_NONE,
        self::FORMATTER_SLUG,
    ];

    /**
     * @param array $headings
     *
     * @return array
     */
    public static function format(array $headings): array
    {
        return (new Collection($headings))->map(function ($value) {
            return static::callFormatter($value);
        })->toArray();
    }

    /**
     * @param string $name
     */
    public static function default(string $name = self::FORMATTER_SLUG)
    {
        if (!isset(static::$customFormatters[$name]) && !in_array($name, static::$defaultFormatters, true)) {
            throw new InvalidArgumentException(sprintf('Formatter "%s" does not exist', $name));
        }

        static::$formatter = $name;
    }

    /**
     * @param string   $name
     * @param callable $formatter
     */
    public static function extend(string $name, callable $formatter)
    {
        static::$customFormatters[$name] = $formatter;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected static function callFormatter($value)
    {
        // Call custom formatter
        if (isset(static::$customFormatters[static::$formatter])) {
            $formatter = static::$customFormatters[static::$formatter];

            return $formatter($value);
        }

        switch (static::$formatter) {
            case self::FORMATTER_SLUG:
                return str_slug($value);
        }

        // No formatter (FORMATTER_NONE)
        return $value;
    }
}