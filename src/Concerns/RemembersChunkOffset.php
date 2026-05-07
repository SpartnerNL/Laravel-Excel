<?php

namespace Maatwebsite\Excel\Concerns;

trait RemembersChunkOffset
{
    /**
     * @var int|null
     */
    protected $chunkOffset;

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
