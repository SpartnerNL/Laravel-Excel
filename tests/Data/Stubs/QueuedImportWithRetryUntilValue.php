<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class QueuedImportWithRetryUntilValue implements ShouldQueue, ToModel, WithChunkReading
{
    use Importable;

    public function __construct(
        private readonly DateTimeInterface|int $until,
    ) {
    }

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

    public function retryUntil(): DateTimeInterface|int
    {
        return $this->until;
    }
}
