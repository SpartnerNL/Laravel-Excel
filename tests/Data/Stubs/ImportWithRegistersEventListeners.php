<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ImportWithRegistersEventListeners implements WithEvents
{
    use Importable, RegistersEventListeners;

    /**
     * @var callable
     */
    public static $beforeRead;

    /**
     * @var callable
     */
    public static $afterRead;

    /**
     * @var callable
     */
    public static $beforeImport;

    /**
     * @var callable
     */
    public static $afterImport;

    /**
     * @var callable
     */
    public static $beforeSheet;

    /**
     * @var callable
     */
    public static $afterSheet;

    // New
    public static function beforeRead()
    {
        (static::$beforeRead)(...func_get_args());
    }

    // New
    public static function afterRead()
    {
        (static::$afterRead)(...func_get_args());
    }

    public static function beforeImport()
    {
        (static::$beforeImport)(...func_get_args());
    }

    public static function afterImport()
    {
        (static::$afterImport)(...func_get_args());
    }

    public static function beforeSheet()
    {
        (static::$beforeSheet)(...func_get_args());
    }

    public static function afterSheet()
    {
        (static::$afterSheet)(...func_get_args());
    }
}
