<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Exceptions;

use InvalidArgumentException;
use Throwable;

class NoFilenameGivenException extends InvalidArgumentException implements LaravelExcelException
{
    public function __construct(
        string $message = 'A filename needs to be passed in order to download the export',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
