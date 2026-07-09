<?php

namespace Maatwebsite\Excel\Helpers;

class CellHelper
{
    public static function getColumnFromCoordinate(string $coordinate): string
    {
        return preg_replace('/\d/', '', $coordinate);
    }
}
