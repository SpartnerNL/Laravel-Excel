<?php

namespace Maatwebsite\Excel\Exceptions;

use Exception;
use Throwable;

class InvalidFileType extends Exception implements LaravelExcelException
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $message = 'File type was not recognized or is unsupported. Make sure you either pass a valid extension to the filename or pass an explicit type.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
