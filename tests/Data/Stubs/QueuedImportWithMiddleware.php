<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class QueuedImportWithMiddleware implements ShouldQueue, ToModel, WithChunkReading
{
    use Importable;

    public function model(array $row): Group
    {
        return new Group([
            'name' => $row[0],
        ]);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [function (): void {
            throw new \Exception('Job reached middleware method');
        }];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
