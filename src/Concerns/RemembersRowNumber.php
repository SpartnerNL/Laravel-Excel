<?php

namespace Maatwebsite\Excel\Concerns;

trait RemembersRowNumber
{
    protected $rowNumber;

    /**
     * @param int $rowNumber
     */
    public function rememberRowNumber(int $rowNumber)
    {
        $this->rowNumber = $rowNumber;
    }

    /**
     *
     */
    public function getRowNumber()
    {
        return $this->rowNumber;
    }
}
