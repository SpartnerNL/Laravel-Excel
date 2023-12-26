<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\AfterBatch;
use Maatwebsite\Excel\Events\AfterChunk;

class ImportWithEventsChunksAndBatches extends ImportWithEvents implements WithBatchInserts, ToModel, WithChunkReading
{
    /**
     * @var callable
     */
    public $afterBatch;

    /**
     * @var callable
     */
    public $afterChunk;

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return parent::registerEvents() + [
            AfterBatch::class => $this->afterBatch ?? function () {
            },
            AfterChunk::class => $this->afterChunk ?? function () {
            },
        ];
    }

    public function model(array $row)
    {
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
