<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use PhpOffice\PhpSpreadsheet\Cell as PhpSpreadsheetCell;

class Cell
{
    /**
     * @var PhpSpreadsheetCell
     */
    private $cell;

    /**
     * @param PhpSpreadsheetCell $cell
     */
    public function __construct(PhpSpreadsheetCell $cell)
    {
        $this->cell = $cell;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->cell->getValue();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }
}
