<?php

namespace Maatwebsite\Excel\Exceptions;

use LogicException;

class NoSheetsFoundException extends LogicException implements LaravelExcelException
{
}
