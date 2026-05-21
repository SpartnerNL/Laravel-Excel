<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class QueuedImportWithMiddleware implements ShouldQueue, ToModel, WithChunkReading
{
    use Importable;

    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        return new Group([
            'name' => $row[0],
        ]);
    }

    public function middleware()
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
