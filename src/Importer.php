<?php

namespace Maatwebsite\Excel;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface Importer
{
    /**
     * @param object              $import
     * @param string|UploadedFile $filePath
     * @param string|null         $disk
     * @param string|null         $readerType
     *
     * @return Reader|PendingDispatch
     */
    public function import($import, $filePath, string $disk = null, string $readerType = null);

    /**
     * @param object              $import
     * @param string|UploadedFile $filePath
     * @param string|null         $disk
     * @param string|null         $readerType
     *
     * @return array
     */
    public function toArray($import, $filePath, string $disk = null, string $readerType = null): array;

    /**
     * @param object              $import
     * @param string|UploadedFile $filePath
     * @param string|null         $disk
     * @param string|null         $readerType
     *
     * @return Collection
     */
    public function toCollection($import, $filePath, string $disk = null, string $readerType = null): Collection;

    /**
     * @param ShouldQueue         $import
     * @param string|UploadedFile $filePath
     * @param string|null         $disk
     * @param string              $readerType
     *
     * @return PendingDispatch
     */
    public function queueImport(ShouldQueue $import, $filePath, string $disk = null, string $readerType = null);
}
