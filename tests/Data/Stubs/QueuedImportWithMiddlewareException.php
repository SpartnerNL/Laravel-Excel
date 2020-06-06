<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class QueuedImportWithMiddlewareException implements ShouldQueue, ToModel, WithChunkReading
{
    use Importable;

    /**
     * @param array $row
     *
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
        return [function() {
            throw new \Exception('Something went wrong in the middleware');
        }];
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
