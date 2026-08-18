<?php

declare(strict_types=1);

namespace Maatwebsite\Excel;

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Import;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface Importer
{
    /**
     * When no disk is given, a string $filePath is read from the local filesystem, not from a disk.
     *
     * @throws ValidationException
     */
    public function import(Import $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): static|PendingDispatch|PendingBatch;

    /**
     * When no disk is given, a string $filePath is read from the local filesystem, not from a disk.
     *
     * @return array<array-key, array<int, array<array-key, mixed>>>
     */
    public function toArray(Import $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): array;

    /**
     * When no disk is given, a string $filePath is read from the local filesystem, not from a disk.
     *
     * @return Collection<array-key, Collection<int, Collection<array-key, mixed>>>
     */
    public function toCollection(?Import $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): Collection;

    /**
     * When no disk is given, a string $filePath is read from the local filesystem, not from a disk.
     */
    public function queueImport(ShouldQueue&Import $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): PendingDispatch|PendingBatch;
}
