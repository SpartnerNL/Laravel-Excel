<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Excel;
use InvalidArgumentException;
use Illuminate\Foundation\Bus\PendingDispatch;

trait Exportable
{
    /**
     * @param string      $fileName
     * @param string|null $writerType
     *
     * @throws InvalidArgumentException
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $fileName = null, string $writerType = null)
    {
        $fileName = $fileName ?? $this->fileName ?? null;

        if (null === $fileName) {
            throw new InvalidArgumentException('A filename needs to be passed in order to download the export');
        }

        return resolve(Excel::class)->download($this, $fileName, $writerType ?? $this->writerType ?? null);
    }

    /**
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     *
     * @throws InvalidArgumentException
     * @return bool|PendingDispatch
     */
    public function store(string $filePath = null, string $disk = null, string $writerType = null)
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new InvalidArgumentException('A filepath needs to be passed in order to store the export');
        }

        return resolve(Excel::class)->store(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $writerType ?? $this->writerType ?? null
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
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new InvalidArgumentException('A filepath needs to be passed in order to store the export');
        }

        return resolve(Excel::class)->queue(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $writerType ?? $this->writerType ?? null
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
