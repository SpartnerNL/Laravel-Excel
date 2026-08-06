<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Exceptions\NoFilenameGivenException;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;
use Maatwebsite\Excel\Exporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait Exportable
{
    protected ?string $fileName = null;

    protected ?string $writerType = null;

    /**
     * @var array<string, string>|null
     */
    protected ?array $headers = [];

    protected ?string $filePath = null;

    protected ?string $disk = null;

    protected mixed $diskOptions = [];

    /**
     * @param  array<string, string>|null  $headers
     *
     * @throws NoFilenameGivenException
     */
    public function download(?string $fileName = null, ?string $writerType = null, ?array $headers = null): BinaryFileResponse
    {
        $headers ??= $this->headers;
        $fileName ??= $this->fileName;
        $writerType ??= $this->writerType;

        if ($fileName === null) {
            throw new NoFilenameGivenException;
        }

        return $this->getExporter()->download($this, $fileName, $writerType, $headers);
    }

    /**
     * @throws NoFilePathGivenException
     */
    public function store(?string $filePath = null, ?string $disk = null, ?string $writerType = null, mixed $diskOptions = []): bool|PendingDispatch|PendingBatch
    {
        $filePath ??= $this->filePath;

        if ($filePath === null) {
            throw NoFilePathGivenException::export();
        }

        return $this->getExporter()->store(
            $this,
            $filePath,
            $disk ?? $this->disk,
            $writerType ?? $this->writerType,
            $diskOptions ?: $this->diskOptions
        );
    }

    /**
     * @throws NoFilePathGivenException
     */
    public function queue(?string $filePath = null, ?string $disk = null, ?string $writerType = null, mixed $diskOptions = []): PendingDispatch|PendingBatch
    {
        $filePath ??= $this->filePath;

        if ($filePath === null) {
            throw NoFilePathGivenException::export();
        }

        return $this->getExporter()->queue(
            $this,
            $filePath,
            $disk ?? $this->disk,
            $writerType ?? $this->writerType,
            $diskOptions ?: $this->diskOptions
        );
    }

    public function raw(?string $writerType = null): string
    {
        $writerType ??= $this->writerType;

        return $this->getExporter()->raw($this, $writerType);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     *
     * @throws NoFilenameGivenException
     */
    public function toResponse($request): BinaryFileResponse
    {
        return $this->download();
    }

    private function getExporter(): Exporter
    {
        return app(Exporter::class);
    }
}
