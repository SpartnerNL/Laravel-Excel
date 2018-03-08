<?php

namespace Maatwebsite\Excel;

use Illuminate\Foundation\Bus\PendingDispatch;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface Exporter
{
    /**
     * @param object      $export
     * @param string|null $fileName
     * @param string      $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return BinaryFileResponse
     */
    public function download($export, string $fileName, string $writerType = null);

    /**
     * @param object      $export
     * @param string      $filePath
     * @param string|null $disk
     * @param string      $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return bool
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null);

    /**
     * @param object      $export
     * @param string      $filePath
     * @param string|null $disk
     * @param string      $writerType
     *
     * @return PendingDispatch
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null);
}
