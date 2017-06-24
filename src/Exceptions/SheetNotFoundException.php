<?php

namespace Maatwebsite\Excel\Exceptions;

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
}
