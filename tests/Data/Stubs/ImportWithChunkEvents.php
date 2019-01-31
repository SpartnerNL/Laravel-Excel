<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterChunkImport;
use Maatwebsite\Excel\Events\BeforeChunkImport;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ImportWithChunkEvents implements WithChunkReading, WithEvents
{
    use Importable;

    /**
     * @var callable
     */
    public $beforeChunkImport;

    /**
     * @var callable
     */
    public $afterChunkImport;

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeChunkImport::class => $this->beforeChunkImport ?? function () {
            },
            AfterChunkImport::class => $this->afterChunkImport ?? function () {
            },
        ];
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }

    public static function beforeChunkImport()
    {
        (static::$beforeChunkImport)(...func_get_args());
    }

    public static function afterChunkImport()
    {
        (static::$afterChunkImport)(...func_get_args());
    }
}
