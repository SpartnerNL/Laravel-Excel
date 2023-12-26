<?php

namespace Maatwebsite\Excel\Concerns;

trait RemembersRowNumber
{
    /**
     * @var int
     */
    protected $rowNumber;

    /**
     * @param  int  $rowNumber
     */
    public function rememberRowNumber(int $rowNumber)
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
