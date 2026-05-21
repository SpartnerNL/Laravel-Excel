<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;

class ImportWithRegistersEventListeners implements WithEvents
{
    use Importable, RegistersEventListeners;

    /**
     * @var callable
     */
    public static $beforeImport;

    /**
     * @var callable
     */
    public static $beforeSheet;

    /**
     * @var callable
     */
    public static $afterSheet;

    public static function beforeImport(): void
    {
        (static::$beforeImport)(...func_get_args());
    }

    public static function beforeSheet(): void
    {
        (static::$beforeSheet)(...func_get_args());
    }

    public static function afterSheet(): void
    {
        (static::$afterSheet)(...func_get_args());
    }
}
