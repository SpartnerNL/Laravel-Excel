<?php

namespace Maatwebsite\Excel\Facades;

use Illuminate\Support\Facades\Facade;
use Maatwebsite\Excel\Fakes\ExcelFake;

class Excel extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'excel';
    }

    /**
     * Replace the bound instance with a fake.
     *
     * @return void
     */
    public static function fake()
    {
        static::swap(new ExcelFake());
    }
}
