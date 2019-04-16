<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Exporter;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Exceptions\NoFilenameGivenException;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;

trait Exportable
{
    /**
     * @param string      $fileName
     * @param string|null $writerType
     * @param array       $headers
     *
     * @throws NoFilenameGivenException
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $fileName = null, string $writerType = null, array $headers = [])
    {
        $fileName = $fileName ?? $this->fileName ?? null;

        if (null === $fileName) {
            throw new NoFilenameGivenException();
        }

        return $this->getExporter()->download(
            $this,
            $fileName,
            $writerType ?? $this->writerType ?? null,
            $headers
        );
    }

    /**
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     * @param mixed       $diskOptions
     *
     * @throws NoFilePathGivenException
     * @return bool|PendingDispatch
     */
    public function store(string $filePath = null, string $disk = null, string $writerType = null, $diskOptions = [])
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw NoFilePathGivenException::export();
        }

        return $this->getExporter()->store(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $writerType ?? $this->writerType ?? null,
            $diskOptions ?? $this->diskOptions ?? []
        );
    }

    /**
     * @param string|null $filePath
     * @param string|null $disk
     * @param string|null $writerType
     * @param mixed       $diskOptions
     *
     * @throws NoFilePathGivenException
     * @return PendingDispatch
     */
    public function queue(string $filePath = null, string $disk = null, string $writerType = null, $diskOptions = [])
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw NoFilePathGivenException::export();
        }

        return $this->getExporter()->queue(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $writerType ?? $this->writerType ?? null,
            $diskOptions ?? $this->diskOptions ?? []
        );
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @throws NoFilenameGivenException
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return $this->download();
    }

    /**
     * @return Exporter
     */
    private function getExporter(): Exporter
    {
        return app(Exporter::class);
    }
}
