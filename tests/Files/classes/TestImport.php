<?php

use Maatwebsite\Excel\Files\ExcelFile;

class TestImport extends ExcelFile {

    /**
     * Get file to import
     * @return string
     */
    public function getFile()
    {
        return __DIR__ . '/../files/test.csv';
    }

} 