<?php

namespace Maatwebsite\Excel\Concerns;

use InvalidArgumentException;

trait ChunkOffset
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
        if (!$this instanceof WithChunkOffset) {
            throw new InvalidArgumentException('Importable should implement WithChunkOffset to get the Offset.');
        }

        return $this->chunkOffset;
    }
}
