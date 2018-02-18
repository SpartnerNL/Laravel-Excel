<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Excel;

trait Exportable
{
    /**
     * @param string      $fileName
     * @param string|null $writerType
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $fileName, string $writerType = null)
    {
        return resolve(Excel::class)->download($this, $fileName, $writerType);
    }

    /**
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     *
     * @return bool
     */
    public function store(string $filePath, string $disk = null, string $writerType = null)
    {
        return resolve(Excel::class)->store($this, $filePath, $disk, $writerType);
    }
}