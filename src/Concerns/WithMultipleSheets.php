<?php

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(Spreadsheet $spreadsheet): array;
}
