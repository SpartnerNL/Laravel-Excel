<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Excel;
use InvalidArgumentException;

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
     * @throws InvalidArgumentException
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $fileName = null, string $writerType = null)
    {
        $fileName = $fileName ?? $this->fileName;

        if (null === $fileName) {
            throw new InvalidArgumentException('A file name needs to be passed in order to download the export');
        }

        return resolve(Excel::class)->download($this, $fileName, $writerType ?? $this->writerType);
    }

    /**
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     *
     * @throws InvalidArgumentException
     * @return bool
     */
    public function store(string $filePath = null, string $disk = null, string $writerType = null)
    {
        $filePath = $filePath ?? $this->filePath;

        if (null === $filePath) {
            throw new InvalidArgumentException('A file name needs to be passed in order to download the export');
        }

        return resolve(Excel::class)->store(
            $this,
            $filePath,
            $disk ?? $this->disk,
            $writerType ?? $this->writerType
        );
    }

    /**
     * @param string|null $filePath
     * @param string|null $disk
     * @param string|null $writerType
     *
     * @return PendingDispatch
     */
    public function queue(string $filePath = null, string $disk = null, string $writerType = null)
    {
        $filePath = $filePath ?? $this->filePath;

        if (null === $filePath) {
            throw new InvalidArgumentException('A file name needs to be passed in order to download the export');
        }

        return resolve(Excel::class)->queue(
            $this,
            $filePath,
            $disk ?? $this->disk,
            $writerType ?? $this->writerType
        );
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @throws InvalidArgumentException
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return $this->download();
    }
}
