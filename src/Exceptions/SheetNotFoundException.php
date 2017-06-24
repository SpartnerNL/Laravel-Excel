<?php

namespace Maatwebsite\Excel\Exceptions;

use Exception;
use OutOfBoundsException;

class SheetNotFoundException extends OutOfBoundsException
{
    /**
     * @param string $name
     *
     * @return static
     */
    public static function byName(string $name)
    {
        return new static(sprintf('Sheet with name [%s] could not be found.', $name));
    }

    /**
     * @param Exception $e
     *
     * @return static
     */
    public static function fromException(Exception $e)
    {
        return new static($e->getMessage(), $e->getCode(), $e);
    }
}
