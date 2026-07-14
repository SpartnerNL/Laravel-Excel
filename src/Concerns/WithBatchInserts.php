<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithBatchInserts extends Import
{
    public function batchSize(): int;
}
