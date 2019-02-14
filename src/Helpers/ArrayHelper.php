<?php

namespace Maatwebsite\Excel\Helpers;

class ArrayHelper
{
    /**
     * @param array $array
     *
     * @return array
     */
    public static function ensureMultiDimensional(array $array): array
    {
        if (static::isMultiDimensional($array)) {
            return $array;
        }

        return [$array];
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    public static function isMultiDimensional(array $array): bool
    {
        return count($array) !== count($array, COUNT_RECURSIVE);
    }
}
