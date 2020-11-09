<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class QueuedImportWithMaxExceptions implements ShouldQueue, ToModel, WithChunkReading
{
    use Importable;

    public $maxExceptions = 3;

    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        throw new \Exception('Max Exceptions is ' . $this->maxExceptions);

        return new Group([
            'name' => $row[0],
        ]);
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
