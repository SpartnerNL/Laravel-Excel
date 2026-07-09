<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Exceptions;

use LogicException;

class NoSheetsFoundException extends LogicException implements LaravelExcelException
{
}
