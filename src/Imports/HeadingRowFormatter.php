<?php

namespace Maatwebsite\Excel\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class HeadingRowFormatter
{
    public const string FORMATTER_NONE = 'none';

    public const string FORMATTER_SLUG = 'slug';

    protected static ?string $formatter;

    /**
     * @var callable[]
     */
    protected static array $customFormatters = [];

    /**
     * @var list<string>
     */
    protected static array $defaultFormatters = [
        self::FORMATTER_NONE,
        self::FORMATTER_SLUG,
    ];

    /**
     * @param  array<array-key, mixed>  $headings
     * @return array<array-key, mixed>
     */
    public static function format(array $headings): array
    {
        return (new Collection($headings))->map(fn ($value, int|string|null $key): mixed => static::callFormatter($value, $key))->toArray();
    }

    public static function default(?string $name = null): void
    {
        if ($name !== null && !isset(static::$customFormatters[$name]) && !in_array($name, static::$defaultFormatters, true)) {
            throw new InvalidArgumentException(sprintf('Formatter "%s" does not exist', $name));
        }

        static::$formatter = $name;
    }

    public static function extend(string $name, callable $formatter): void
    {
        static::$customFormatters[$name] = $formatter;
    }

    /**
     * Reset the formatter.
     */
    public static function reset(): void
    {
        static::default();
    }

    protected static function callFormatter(mixed $value, int|string|null $key = null): mixed
    {
        static::$formatter ??= config('excel.imports.heading_row.formatter', self::FORMATTER_SLUG);

        // Call custom formatter
        if (isset(static::$customFormatters[static::$formatter])) {
            $formatter = static::$customFormatters[static::$formatter];

            return $formatter($value, $key);
        }

        if (empty($value)) {
            return $key;
        }

        if (static::$formatter === self::FORMATTER_SLUG) {
            return Str::slug($value, '_');
        }

        // No formatter (FORMATTER_NONE)
        return $value;
    }
}
