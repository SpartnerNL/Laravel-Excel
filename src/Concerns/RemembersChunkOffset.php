<?php

namespace Maatwebsite\Excel\Concerns;

trait RemembersChunkOffset
{
    protected ?int $chunkOffset;

    public function setChunkOffset(int $chunkOffset): void
    {
        $this->chunkOffset = $chunkOffset;
    }

    public function getChunkOffset(): ?int
    {
        return $this->chunkOffset;
    }
}
