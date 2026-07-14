<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithCustomChunkSize extends Export
{
    public function chunkSize(): int;
}
