<?php

namespace Maatwebsite\Excel\Concerns;

use InvalidArgumentException;

trait RemembersRowNumber
{
    /**
     * @var int|null
     */
    protected $rowNumber;

    /**
     * @param int $rowNumber
     */
    public function setRowNumber(int $rowNumber)
    {
        $this->rowNumber = $rowNumber;
    }

    /**
     * @return int|null
     */
    public function getRowNumber()
    {
        return $this->rowNumber;
    }
}
