<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Exceptions;

use LogicException;

final class ConcernConflictException extends LogicException implements LaravelExcelException
{
    public static function queryOrCollectionAndView(): ConcernConflictException
    {
        return new self('Cannot use FromQuery, FromScout, FromArray or FromCollection and FromView on the same sheet.');
    }

    public static function columnsAndHeadings(): ConcernConflictException
    {
        return new self("Cannot use WithColumns and WithHeadings on the same sheet. The columns already define the heading row; use the column title instead: Text::make('Full Name').");
    }

    public static function columnsAndMapping(): ConcernConflictException
    {
        return new self("Cannot use WithColumns and WithMapping on the same export. The columns already map each value; pass a callback instead: Text::make('Name', fn (\$row) => \$row->name).");
    }

    public static function columnsAndMappedCells(): ConcernConflictException
    {
        return new self('Cannot use WithColumns and WithMappedCells on the same sheet. WithMappedCells reads scattered coordinates, while columns read a range of rows.');
    }

    public static function columnsAndColumnLimit(): ConcernConflictException
    {
        return new self('Cannot use WithColumns and WithColumnLimit on the same sheet. The columns already determine which range is read.');
    }

    public static function columnsAndGroupedHeadingRow(): ConcernConflictException
    {
        return new self('Cannot use WithColumns and WithGroupedHeadingRow on the same sheet. Grouping collects several cells under one heading, which a column cannot represent; use Column::multiple() to read several values from a single cell.');
    }
}
