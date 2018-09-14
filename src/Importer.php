<?php

namespace Maatwebsite\Excel;

use Illuminate\Foundation\Bus\PendingDispatch;

interface Importer
{
    /**
     * @param object      $import
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     *
     * @return PendingDispatch|Importer
     */
    public function import($import, string $filePath, string $disk = null, string $writerType = null);

    /**
     * @param object      $import
     * @param string      $filePath
     * @param string|null $disk
     * @param string      $writerType
     *
     * @return PendingDispatch
     */
    public function queueImport($import, string $filePath, string $disk = null, string $writerType = null);
}
