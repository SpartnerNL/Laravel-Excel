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
     * @param array       $headers
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return BinaryFileResponse
     */
    public function download($export, string $fileName, string $writerType = null, array $headers = []);

    /**
     * @param object      $export
     * @param string      $filePath
     * @param string|null $disk
     * @param string      $writerType
     * @param mixed       $diskOptions
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return bool
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null, $diskOptions = []);

    /**
     * @param object      $export
     * @param string      $filePath
     * @param string|null $disk
     * @param string      $writerType
     * @param mixed       $diskOptions
     *
     * @return PendingDispatch
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null, $diskOptions = []);

    /**
     * @param object $export
     * @param string $writerType
     *
     * @return string
     */
    public function raw($export, string $writerType);
}
