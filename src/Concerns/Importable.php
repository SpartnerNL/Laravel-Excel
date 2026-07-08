<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Bus\PendingBatch;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;
use Maatwebsite\Excel\Importer;
use Maatwebsite\Excel\Validators\ValidationException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait Importable
{
    protected OutputStyle $output;

    protected ?string $disk = null;

    protected ?string $readerType = null;

    protected string|UploadedFile|null $filePath = null;

    /**
     * @throws ValidationException
     * @throws NoFilePathGivenException
     */
    public function import(string|UploadedFile|null $filePath = null, ?string $disk = null, ?string $readerType = null): Importer|PendingDispatch|PendingBatch
    {
        $filePath = $this->getFilePath($filePath);

        return $this->getImporter()->import(
            $this,
            $filePath,
            $disk ?? $this->disk,
            $readerType ?? $this->readerType
        );
    }

    /**
     * @return array<array-key, array<int, array<array-key, mixed>>>
     *
     * @throws NoFilePathGivenException
     */
    public function toArray(string|UploadedFile|null $filePath = null, ?string $disk = null, ?string $readerType = null): array
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
     * @return Collection<array-key, Collection<int, Collection<array-key, mixed>>>
     *
     * @throws NoFilePathGivenException
     */
    public function toCollection(string|UploadedFile|null $filePath = null, ?string $disk = null, ?string $readerType = null): Collection
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
     * @throws NoFilePathGivenException
     * @throws InvalidArgumentException
     */
    public function queue(string|UploadedFile|null $filePath = null, ?string $disk = null, ?string $readerType = null): PendingDispatch|PendingBatch
    {
        if (!$this instanceof ShouldQueue) {
            throw new InvalidArgumentException('Importable should implement ShouldQueue to be queued.');
        }

        return $this->getImporter()->queueImport(
            $this,
            $this->getFilePath($filePath),
            $disk ?? $this->disk,
            $readerType ?? $this->readerType
        );
    }

    /**
     * @return $this
     */
    public function withOutput(OutputStyle $output)
    {
        $this->output = $output;

        return $this;
    }

    public function getConsoleOutput(): OutputStyle
    {
        return $this->output ??= new OutputStyle(new StringInput(''), new NullOutput);
    }

    /**
     * @throws NoFilePathGivenException
     */
    private function getFilePath(UploadedFile|string|null $filePath = null): UploadedFile|string
    {
        $filePath ??= $this->filePath ?? null;

        if ($filePath === null) {
            throw NoFilePathGivenException::import();
        }

        return $filePath;
    }

    private function getImporter(): Importer
    {
        return app(Importer::class);
    }
}
