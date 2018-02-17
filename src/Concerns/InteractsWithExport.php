<?php

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface InteractsWithExport
{
    /**
     * @param Spreadsheet $spreadsheet
     */
    public function interact(Spreadsheet $spreadsheet);
}