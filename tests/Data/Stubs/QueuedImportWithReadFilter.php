<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class QueuedImportWithReadFilter implements ShouldQueue, ToArray, WithChunkReading, WithReadFilter
{
    use Importable;

    /**
     * @var array<int, array<array-key, mixed>>
     */
    public static array $rows = [];

    public function array(array $array): void
    {
        static::$rows = [...static::$rows, ...$array];
    }

    public function chunkSize(): int
    {
        return 1;
    }

    public function readFilter(): IReadFilter
    {
        return new FirstColumnReadFilter;
    }
}
