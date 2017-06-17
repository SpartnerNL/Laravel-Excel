<?php

namespace Maatwebsite\Excel\Bridge\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Excel extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'excel';
    }
}
