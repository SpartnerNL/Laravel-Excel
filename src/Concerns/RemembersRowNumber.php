<?php

namespace Maatwebsite\Excel\Concerns;

trait RemembersRowNumber
{
    protected int $rowNumber;

    public function rememberRowNumber(int $rowNumber): void
    {
        $this->rowNumber = $rowNumber;
    }

    public function getRowNumber(): ?int
    {
        return $this->rowNumber;
    }
}
