<?php

namespace Maatwebsite\Excel\Concerns;

interface RemembersRowNumber
{
    /**
     * @param int $rowNumber
     */
    public function setRowNumber(int $rowNumber);
}
