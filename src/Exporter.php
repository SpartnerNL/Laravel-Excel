<?php

namespace Maatwebsite\Excel;

use Illuminate\Foundation\Bus\PendingDispatch;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface Exporter
{
    /**
     * @param  object  $export
     * @param  string|null  $fileName
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function download($export, string $fileName, ?string $writerType = null, array $headers = []);

    /**
     * @param  object  $export
     * @param  mixed  $diskOptions
     * @return bool
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function store($export, string $filePath, ?string $diskName = null, ?string $writerType = null, $diskOptions = []);

    /**
     * @param  object  $export
     * @param  mixed  $diskOptions
     * @return PendingDispatch
     */
    public function queue($export, string $filePath, ?string $disk = null, ?string $writerType = null, $diskOptions = []);

    /**
     * @param  object  $export
     * @return string
     */
    public function raw($export, string $writerType);
}
