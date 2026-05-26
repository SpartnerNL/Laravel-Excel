<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Exceptions;

use Exception;
use Throwable;

class NoTypeDetectedException extends Exception implements LaravelExcelException
{
    public function __construct(
        string $message = 'No ReaderType or WriterType could be detected. Make sure you either pass a valid extension to the filename or pass an explicit type.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
