<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Exceptions;

use Exception;
use Throwable;

class UnreadableFileException extends Exception implements LaravelExcelException
{
    public function __construct(
        string $message = 'File could not be read',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
