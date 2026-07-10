<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Exceptions;

use Exception;

final class SheetNotFoundException extends Exception implements LaravelExcelException
{
    public static function byName(string $name): SheetNotFoundException
    {
        return new self("Your requested sheet name [{$name}] is out of bounds.");
    }

    public static function byIndex(int $index, int $sheetCount): SheetNotFoundException
    {
        return new self("Your requested sheet index: {$index} is out of bounds. The actual number of sheets is {$sheetCount}.");
    }
}
