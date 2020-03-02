<?php

namespace Maatwebsite\Excel\Concerns;

/**
 * @deprecated since 3.2
 */
interface WithCustomChunkSize
{
    /**
     * @return int
     */
    public function chunkSize(): int;
}
