<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Exceptions;

use LogicException;

final class UnsupportedColumnException extends LogicException implements LaravelExcelException
{
    public static function multipleOnExport(): UnsupportedColumnException
    {
        return new self('Column::multiple() reads several values from a single cell and can only be used on an import. Give each value its own column to export them.');
    }
}
