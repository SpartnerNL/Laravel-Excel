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
}
