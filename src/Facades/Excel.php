<?php

namespace Maatwebsite\Excel\Facades;

use Illuminate\Support\Facades\Facade;
use Maatwebsite\Excel\Fakes\ExcelFake;
use Maatwebsite\Excel\Fakes\WriterFake;
use Maatwebsite\Excel\Fakes\QueuedWriterFake;
use Illuminate\Contracts\Routing\ResponseFactory;

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
        static::swap(new ExcelFake(
            app()->make(WriterFake::class),
            app()->make(QueuedWriterFake::class),
            app()->make(ResponseFactory::class),
            app()->make('filesystem')
        ));
    }
}
