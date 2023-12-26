<?php

namespace Maatwebsite\Excel\Exceptions;

class SheetNotFoundException extends \Exception implements LaravelExcelException
{
    /**
     * @param  string  $name
     * @return SheetNotFoundException
     */
    public static function byName(string $name): SheetNotFoundException
    {
        return new static("Your requested sheet name [{$name}] is out of bounds.");
    }

    /**
     * @param  int  $index
     * @param  int  $sheetCount
     * @return SheetNotFoundException
     */
    public static function byIndex(int $index, int $sheetCount): SheetNotFoundException
    {
        return new static("Your requested sheet index: {$index} is out of bounds. The actual number of sheets is {$sheetCount}.");
    }
}
