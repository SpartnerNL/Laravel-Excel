<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use PhpOffice\PhpSpreadsheet\Worksheet;

class Sheet
{
    /**
     * @var Worksheet
     */
    private $worksheet;

    /**
     * @param Worksheet $worksheet
     */
    public function __construct(Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
    }
}