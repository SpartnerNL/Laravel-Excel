<?php

namespace Maatwebsite\Excel\Concerns;

use InvalidArgumentException;
use Maatwebsite\Excel\Importer;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;

trait Importable
{
    /**
     * @param string|UploadedFile|null $filePath
     * @param string|null              $disk
     * @param string|null              $readerType
     *
     * @throws NoFilePathGivenException
     * @return Importer|PendingDispatch
     */
    public function import($filePath = null, string $disk = null, string $readerType = null)
    {
        $filePath = $this->getFilePath($filePath);

        return $this->getImporter()->import(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $readerType ?? $this->readerType ?? null
        );
    }

    /**
     * @param string|UploadedFile|null $filePath
     * @param string|null              $disk
     * @param string|null              $readerType
     *
     * @throws NoFilePathGivenException
     * @return array
     */
    public function toArray($filePath = null, string $disk = null, string $readerType = null): array
    {
        $filePath = $this->getFilePath($filePath);

        return $this->getImporter()->toArray(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $readerType ?? $this->readerType ?? null
        );
    }

    /**
     * @param string|UploadedFile|null $filePath
     * @param string|null              $disk
     * @param string|null              $readerType
     *
     * @throws NoFilePathGivenException
     * @return Collection
     */
    public function toCollection($filePath = null, string $disk = null, string $readerType = null): Collection
    {
        $filePath = $this->getFilePath($filePath);

        return $this->getImporter()->toCollection(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $readerType ?? $this->readerType ?? null
        );
    }

    /**
     * @param string|UploadedFile|null $filePath
     * @param string|null              $disk
     * @param string|null              $readerType
     *
     * @throws NoFilePathGivenException
     * @throws InvalidArgumentException
     * @return PendingDispatch
     */
    public function queue($filePath = null, string $disk = null, string $readerType = null)
    {
        if (!$this instanceof ShouldQueue) {
            throw new InvalidArgumentException('Importable should implement ShouldQueue to be queued.');
        }

        return $this->import($filePath, $disk, $readerType);
    }

    /**
     * @param string|null $filePath
     *
     * @throws NoFilePathGivenException
     * @return string
     */
    private function getFilePath(string $filePath = null): string
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new NoFilePathGivenException();
        }

        return $filePath;
    }

    /**
     * @return Importer
     */
    private function getImporter(): Importer
    {
        return resolve(Importer::class);
    }
}
