<?php

namespace Maatwebsite\Excel\Exceptions;

use LogicException;

class ConcernConflictException extends LogicException implements LaravelExcelException
{
    /**
     * @return ConcernConflictException
     */
    public static function queryOrCollectionAndView()
    {
        return new static('Cannot use FromQuery or FromCollection and FromView on the same sheet.');
    }
}
