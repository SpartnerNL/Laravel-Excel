<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Exceptions;

use LogicException;

final class ColumnCollisionException extends LogicException implements LaravelExcelException
{
    public static function atLetter(string $letter): ColumnCollisionException
    {
        return new self(sprintf(
            'Multiple columns were placed on column %s. Give each column its own letter, or combine them with Column::multiple().',
            $letter
        ));
    }
}
