<?php

namespace Maatwebsite\Excel\Bridge\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Excel extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'excel';
    }
}