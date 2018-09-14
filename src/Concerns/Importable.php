<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use InvalidArgumentException;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;

trait Importable
{
    /**
     * @param string|null $filePath
     * @param string|null $disk
     * @param string|null $readerType
     *
     * @throws NoFilePathGivenException
     * @return Excel|PendingDispatch
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
            $readerType ?? $this->readerType ?? null
        );
    }

    /**
     * @param string|null $filePath
     * @param string|null $disk
     * @param string|null $readerType
     *
     * @throws NoFilePathGivenException
     * @throws InvalidArgumentException
     * @return PendingDispatch
     */
    public function queue(string $filePath = null, string $disk = null, string $readerType = null)
    {
        if (!$this instanceof ShouldQueue) {
            throw new InvalidArgumentException('Importable should implement ShouldQueue to be queued.');
        }

        return $this->import($filePath, $disk, $readerType);
    }
}
