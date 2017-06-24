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
}