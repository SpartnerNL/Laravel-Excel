<?php

namespace Maatwebsite\Excel\Exceptions;

use LogicException;

final class ConcernConflictException extends LogicException implements LaravelExcelException
{
    /**
     * @return ConcernConflictException
     */
    public static function queryOrCollectionAndView()
    {
        return new self('Cannot use FromQuery, FromArray or FromCollection and FromView on the same sheet.');
    }
}
