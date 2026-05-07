<?php

namespace Maatwebsite\Excel\Concerns;

interface WithChunkReading
{
    public function chunkSize(): int;
}
