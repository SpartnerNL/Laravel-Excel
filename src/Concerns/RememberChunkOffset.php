<?php

namespace Maatwebsite\Excel\Concerns;

trait RememberChunkOffset
{
    /**
     * @var int|null
     */
    protected $chunkOffset;

    /**
     * @param int $chunkOffset
     */
    public function setChunkOffset(int $chunkOffset)
    {
        $this->chunkOffset = $chunkOffset;
    }

    /**
     * @return int|null
     */
    public function getChunkOffset()
    {
        return $this->chunkOffset;
    }
}
