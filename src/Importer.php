<?php

declare(strict_types=1);

namespace Maatwebsite\Excel;

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface Importer
{
    /**
     * @throws ValidationException
     */
    public function import(object $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): static|PendingDispatch|PendingBatch;

    /**
     * @return array<array-key, array<int, array<array-key, mixed>>>
     */
    public function toArray(object $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): array;

    /**
     * @return Collection<array-key, Collection<int, Collection<array-key, mixed>>>
     */
    public function toCollection(?object $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): Collection;

    public function queueImport(ShouldQueue $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): PendingDispatch|PendingBatch;
}
