<?php

use Maatwebsite\Excel\Files\NewExcelFile;

class TestExport extends NewExcelFile {

    /**
     * Get file to import
     * @return string
     */
    public function getFilename()
    {
        return 'test-file';
    }

} 