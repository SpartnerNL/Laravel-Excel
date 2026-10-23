<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class QueuedImportWithDateBackoff implements ShouldQueue, ToModel, WithChunkReading
{
    use Importable;

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

    public function backoff(): DateTimeInterface
    {
        return CarbonImmutable::now()->addSeconds(30);
    }
}
