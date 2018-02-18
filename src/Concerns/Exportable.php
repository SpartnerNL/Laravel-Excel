<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Excel;

trait Exportable
{
    /**
     * @var string|null
     */
    protected $fileName;

    /**
     * @var string|null
     */
    protected $writerType;

    /**
     * @var string|null
     */
    protected $filePath;

    /**
     * @var string|null
     */
    protected $disk;

    /**
     * @param string      $fileName
     * @param string|null $writerType
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $fileName = null, string $writerType = null)
    {
        return resolve(Excel::class)->download($this, $fileName ?? $this->fileName, $writerType ?? $this->writerType);
    }

    /**
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     *
     * @return bool
     */
    public function store(string $filePath = null, string $disk = null, string $writerType = null)
    {
        return resolve(Excel::class)->store($this, $filePath ?? $this->filePath, $disk ?? $this->disk, $writerType ?? $this->writerType);
    }
}
