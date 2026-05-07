<?php

namespace Maatwebsite\Excel\Concerns;

interface WithCustomChunkSize
{
    public function chunkSize(): int;
}
