<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;

class ExportWithRegistersEventListeners implements WithEvents
{
    use Exportable, RegistersEventListeners;

    /**
     * @var callable
     */
    public static $beforeExport;

    /**
     * @var callable
     */
    public static $beforeWriting;

    /**
     * @var callable
     */
    public static $beforeSheet;

    /**
     * @var callable
     */
    public static $afterSheet;

    public static function beforeExport(): void
    {
        (static::$beforeExport)(...func_get_args());
    }

    public static function beforeWriting(): void
    {
        (static::$beforeWriting)(...func_get_args());
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
