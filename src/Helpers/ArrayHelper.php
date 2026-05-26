<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Helpers;

class ArrayHelper
{
    public static function ensureMultipleRows(array $array): array
    {
        if (static::hasMultipleRows($array)) {
            return $array;
        }

        return [$array];
    }

    /**
     * Only have multiple rows, if each
     * element in the array is an array itself.
     */
    public static function hasMultipleRows(array $array): bool
    {
        return count($array) === count(array_filter($array, is_array(...)));
    }
}
