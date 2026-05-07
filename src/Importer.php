<?php

namespace Maatwebsite\Excel;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface Importer
{
    /**
     * @param  object  $import
     * @param  string|UploadedFile  $filePath
     * @return Reader|PendingDispatch
     */
    public function import($import, $filePath, ?string $disk = null, ?string $readerType = null);

    /**
     * @param  object  $import
     * @param  string|UploadedFile  $filePath
     */
    public function toArray($import, $filePath, ?string $disk = null, ?string $readerType = null): array;

    /**
     * @param  object  $import
     * @param  string|UploadedFile  $filePath
     */
    public function toCollection($import, $filePath, ?string $disk = null, ?string $readerType = null): Collection;

    /**
     * @param  string|UploadedFile  $filePath
     * @return PendingDispatch
     */
    public function queueImport(ShouldQueue $import, $filePath, ?string $disk = null, ?string $readerType = null);
}
