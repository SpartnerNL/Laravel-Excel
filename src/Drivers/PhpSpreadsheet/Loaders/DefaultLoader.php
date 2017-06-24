<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DefaultLoader
{
    /**
     * @param string $filePath
     *
     * @return Spreadsheet
     */
    public function __invoke(string $filePath): Spreadsheet
    {
        return IOFactory::load($filePath);
    }
}
