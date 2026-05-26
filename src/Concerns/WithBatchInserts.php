<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithBatchInserts
{
    public function batchSize(): int;
}
