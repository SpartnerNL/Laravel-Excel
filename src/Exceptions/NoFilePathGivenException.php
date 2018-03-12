<?php

namespace Maatwebsite\Excel\Exceptions;

use InvalidArgumentException;
use Throwable;

class NoFilePathGivenException extends InvalidArgumentException implements LaravelExcelException
{
    /**
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $message = 'A filepath needs to be passed in order to store the export',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}