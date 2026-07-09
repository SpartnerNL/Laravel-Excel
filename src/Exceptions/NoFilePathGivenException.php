<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Exceptions;

use InvalidArgumentException;
use Throwable;

class NoFilePathGivenException extends InvalidArgumentException implements LaravelExcelException
{
    final public function __construct(
        string $message = 'A filepath needs to be passed.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function import(): NoFilePathGivenException
    {
        return new static('A filepath or UploadedFile needs to be passed to start the import.');
    }

    public static function export(): NoFilePathGivenException
    {
        return new static('A filepath needs to be passed in order to store the export.');
    }
}
