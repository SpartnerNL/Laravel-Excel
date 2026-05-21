<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Exceptions\NoFilenameGivenException;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;
use Maatwebsite\Excel\Exporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait Exportable
{
    protected ?string $filePath = null;

    /**
     * @return Response|BinaryFileResponse
     *
     * @throws NoFilenameGivenException
     */
    public function download(?string $fileName = null, ?string $writerType = null, ?array $headers = null)
    {
        $headers ??= $this->headers ?? [];
        $fileName ??= $this->fileName ?? null;
        $writerType ??= $this->writerType ?? null;

        if ($fileName === null) {
            throw new NoFilenameGivenException;
        }

        return $this->getExporter()->download($this, $fileName, $writerType, $headers);
    }

    /**
     * @param  mixed  $diskOptions
     * @return bool|PendingDispatch
     *
     * @throws NoFilePathGivenException
     */
    public function store(?string $filePath = null, ?string $disk = null, ?string $writerType = null, $diskOptions = [])
    {
        $filePath ??= $this->filePath ?? null;

        if ($filePath === null) {
            throw NoFilePathGivenException::export();
        }

        return $this->getExporter()->store(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $writerType ?? $this->writerType ?? null,
            $diskOptions ?: $this->diskOptions ?? []
        );
    }

    /**
     * @param  mixed  $diskOptions
     * @return PendingDispatch
     *
     * @throws NoFilePathGivenException
     */
    public function queue(?string $filePath = null, ?string $disk = null, ?string $writerType = null, $diskOptions = [])
    {
        $filePath ??= $this->filePath ?? null;

        if ($filePath === null) {
            throw NoFilePathGivenException::export();
        }

        return $this->getExporter()->queue(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $writerType ?? $this->writerType ?? null,
            $diskOptions ?: $this->diskOptions ?? []
        );
    }

    /**
     * @param  string|null  $writerType
     * @return string
     */
    public function raw($writerType = null)
    {
        $writerType ??= $this->writerType ?? null;

        return $this->getExporter()->raw($this, $writerType);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response
     *
     * @throws NoFilenameGivenException
     */
    public function toResponse($request)
    {
        return $this->download();
    }

    private function getExporter(): Exporter
    {
        return app(Exporter::class);
    }
}
