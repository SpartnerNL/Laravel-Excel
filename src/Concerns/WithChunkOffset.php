<?php

namespace Maatwebsite\Excel\Concerns;

interface WithChunkOffset
{
    public function setChunkOffset(int $chunkOffset);

    /**
     * @return int
     */
    public function getChunkOffset();
}