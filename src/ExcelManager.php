<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Manager;
use Maatwebsite\Excel\Drivers\PHPExcel\Excel as PHPExcel;

class ExcelManager extends Manager
{
    /**
     * @return PHPExcel
     */
    public function createPhpexcelDriver()
    {
        return $this->app->make(PHPExcel::class);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'phpexcel';
    }
}
