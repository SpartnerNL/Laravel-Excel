<?php

namespace Maatwebsite\Excel\Helpers;

class CellHelper
{
    public static function getColumnFromCoordinate(string $coordinate): string
    {
        return preg_replace('/[0-9]/', '', $coordinate);
    }
}
