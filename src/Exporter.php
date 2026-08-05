<?php

declare(strict_types=1);

namespace Maatwebsite\Excel;

use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Concerns\Export;
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
    public function download(Export $export, string $fileName, ?string $writerType = null, array $headers = []): BinaryFileResponse;

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function store(Export $export, string $filePath, ?string $diskName = null, ?string $writerType = null, mixed $diskOptions = []): bool|PendingDispatch|PendingBatch;

    public function queue(Export $export, string $filePath, ?string $disk = null, ?string $writerType = null, mixed $diskOptions = []): PendingDispatch|PendingBatch;

    public function raw(Export $export, string $writerType): string;
}
