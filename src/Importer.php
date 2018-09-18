<?php

namespace Maatwebsite\Excel;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface Importer
{
    /**
     * @param object              $import
     * @param string|UploadedFile $filePath
     * @param string|null         $disk
     * @param string|null         $writerType
     *
     * @return PendingDispatch|Importer
     */
    public function import($import, $filePath, string $disk = null, string $writerType = null);

    /**
     * @param ShouldQueue         $import
     * @param string|UploadedFile $filePath
     * @param string|null         $disk
     * @param string              $writerType
     *
     * @return PendingDispatch
     */
    public function queueImport(ShouldQueue $import, $filePath, string $disk = null, string $writerType = null);
}
