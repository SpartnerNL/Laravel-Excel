<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class QueuedImportWithBackoff implements ShouldQueue, ToModel, WithChunkReading
{
    use Importable;

    /**
     * @var array<int, int>
     */
    public array $backoff = [1, 5, 10];

    public function model(array $row): Group
    {
        return new Group([
            'name' => $row[0],
        ]);
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
