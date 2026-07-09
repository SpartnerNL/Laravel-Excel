<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithCustomChunkSize
{
    public function chunkSize(): int;
}
