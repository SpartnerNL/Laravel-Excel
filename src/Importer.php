<?php

namespace Maatwebsite\Excel;

use Illuminate\Foundation\Bus\PendingDispatch;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface Importer
{
    /**
     * @param object      $import
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     *
     * @return bool
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
    public function queuedImport($import, string $filePath, string $disk = null, string $writerType = null);
}
