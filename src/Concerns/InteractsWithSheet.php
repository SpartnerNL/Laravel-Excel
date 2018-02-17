<?php

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface InteractsWithSheet
{
    /**
     * @param Worksheet $sheet
     */
    public function interactWithSheet(Worksheet $sheet);
}
