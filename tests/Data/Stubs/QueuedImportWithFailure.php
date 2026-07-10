<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class QueuedImportWithFailure implements ShouldQueue, ToModel, WithChunkReading
{
    use Importable;

    public ?int $maxExceptions = null;

    public function model(array $row): ?Model
    {
        throw new Exception('Something went wrong in the chunk');
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
