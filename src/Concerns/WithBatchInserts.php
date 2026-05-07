<?php

namespace Maatwebsite\Excel\Concerns;

interface WithBatchInserts
{
    public function batchSize(): int;
}
