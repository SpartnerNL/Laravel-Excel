<?php

use Maatwebsite\Excel\Files\ExcelFile;

class TestImport extends ExcelFile {

    protected $delimiter  = ',';
    protected $enclosure  = '"';
    protected $lineEnding = '\r\n';

    /**
     * Get file to import
     * @return string
     */
    public function getFile()
    {
        return __DIR__ . '/../files/test.csv';
    }

    /**
     * Get filters
     * @return array
     */
    public function getFilters()
    {
        return [
            'chunk'
        ];
    }

} 