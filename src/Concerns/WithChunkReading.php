<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithChunkReading
{
    public function chunkSize(): int;
}
