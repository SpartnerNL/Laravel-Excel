<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;
use Maatwebsite\Excel\Reader;

trait Importable
{
    /**
     * @param string      $fileName
     * @param string|null $writerType
     * @param string|null $readerType
     *
     * @throws NoFilenameGivenException
     * @return Reader
     */
    public function import(string $filePath = null, string $disk = null, string $readerType = null)
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new NoFilePathGivenException();
        }

        return resolve(Excel::class)->import(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $writerType ?? $this->writerType ?? null
        );
    }

    /**
     * @param string|null $filePath
     * @param string|null $disk
     * @param string|null $readerType
     *
     * @return array
     */
    public function toArray(string $filePath = null, string $disk = null, string $readerType = null)
    {
        return $this->import($filePath, $disk, $readerType)->toArray();
    }

    /**
     * @param string|null $filePath
     * @param string|null $disk
     * @param string|null $readerType
     *
     * @return Collection
     */
    public function toCollection(string $filePath = null, string $disk = null, string $readerType = null)
    {
        return $this->import($filePath, $disk, $readerType)->toCollection();
    }
}
