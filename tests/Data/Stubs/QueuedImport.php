<?php

namespace Seoperin\LaravelExcel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Model;
use Seoperin\LaravelExcel\Concerns\ToModel;
use Seoperin\LaravelExcel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Seoperin\LaravelExcel\Concerns\WithBatchInserts;
use Seoperin\LaravelExcel\Concerns\WithChunkReading;
use Seoperin\LaravelExcel\Tests\Data\Stubs\Database\Group;

class QueuedImport implements ShouldQueue, ToModel, WithChunkReading, WithBatchInserts
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

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
