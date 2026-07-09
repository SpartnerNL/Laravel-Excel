<?php

declare(strict_types=1);

namespace Maatwebsite\Excel;

use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Bus\PendingDispatch;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface Exporter
{
    /**
     * @param  array<string, string>  $headers
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function download(object $export, string $fileName, ?string $writerType = null, array $headers = []): BinaryFileResponse;

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function store(object $export, string $filePath, ?string $diskName = null, ?string $writerType = null, mixed $diskOptions = []): bool|PendingDispatch|PendingBatch;

    public function queue(object $export, string $filePath, ?string $disk = null, ?string $writerType = null, mixed $diskOptions = []): PendingDispatch|PendingBatch;

    public function raw(object $export, string $writerType): string;
}
