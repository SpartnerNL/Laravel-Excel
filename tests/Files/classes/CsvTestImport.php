<?php

use Maatwebsite\Excel\Files\ExcelFile;

class CsvTestImport extends ExcelFile {

    /**
     * Custom delimiter
     * @var string
     */
    protected $delimiter  = ';';

    /**
     * Get file to import
     * @return string
     */
    public function getFile()
    {
        return __DIR__ . '/../files/test-custom.csv';
    }
} 