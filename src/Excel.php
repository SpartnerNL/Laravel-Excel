<?php

namespace Maatwebsite\Excel;

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Files\Filesystem;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Helpers\FileTypeDetector;
use Maatwebsite\Excel\Validators\ValidationException;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Excel implements Exporter, Importer
{
    use Macroable, RegistersCustomConcerns;

    public const string XLSX = 'Xlsx';

    public const string CSV = 'Csv';

    public const string TSV = 'Csv';

    public const string ODS = 'Ods';

    public const string XLS = 'Xls';

    public const string SLK = 'Slk';

    public const string XML = 'Xml';

    public const string GNUMERIC = 'Gnumeric';

    public const string HTML = 'Html';

    public const string MPDF = 'Mpdf';

    public const string DOMPDF = 'Dompdf';

    public const string TCPDF = 'Tcpdf';

    public function __construct(
        protected Writer $writer,
        protected QueuedWriter $queuedWriter,
        private readonly Reader $reader,
        protected Filesystem $filesystem,
        private readonly HandlerRegistry $handlerRegistry,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param  array<string, string>  $headers
     */
    public function download(Export $export, string $fileName, ?string $writerType = null, array $headers = []): BinaryFileResponse
    {
        // Clear output buffer to prevent stuff being prepended to the Excel output.
        if (ob_get_length() > 0) {
            ob_end_clean();
            ob_start();
        }

        return response()->download(
            $this->export($export, $fileName, $writerType)->getLocalPath(),
            $fileName,
            $headers
        )->deleteFileAfterSend(true);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string|null  $disk  Fallback for usage with named properties
     */
    public function store(Export $export, string $filePath, ?string $diskName = null, ?string $writerType = null, mixed $diskOptions = [], ?string $disk = null): bool|PendingDispatch|PendingBatch
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $diskName ?: $disk, $writerType, $diskOptions);
        }

        $temporaryFile = $this->export($export, $filePath, $writerType);

        try {
            return $this->filesystem->disk($diskName ?: $disk, $diskOptions)->copy(
                $temporaryFile,
                $filePath
            );
        } finally {
            $temporaryFile->delete();
        }
    }

    public function queue(Export $export, string $filePath, ?string $disk = null, ?string $writerType = null, mixed $diskOptions = []): PendingDispatch|PendingBatch
    {
        $writerType = FileTypeDetector::detectStrict($filePath, $writerType);

        return $this->queuedWriter->store(
            $export,
            $filePath,
            $disk,
            $writerType,
            $diskOptions
        );
    }

    public function raw(Export $export, string $writerType): string
    {
        $temporaryFile = $this->writer->export($export, $writerType);

        $contents = $temporaryFile->contents();
        $temporaryFile->delete();

        return $contents;
    }

    /**
     * @throws ValidationException
     */
    public function import(Import $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): static|PendingDispatch|PendingBatch
    {
        $readerType = FileTypeDetector::detect($filePath, $readerType);
        $response   = $this->reader->read($import, $filePath, $readerType, $disk);

        if ($response instanceof PendingDispatch || $response instanceof PendingBatch) {
            return $response;
        }

        return $this;
    }

    /**
     * @return array<array-key, array<int, array<array-key, mixed>>>
     */
    public function toArray(Import $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): array
    {
        $readerType = FileTypeDetector::detect($filePath, $readerType);

        return $this->reader->toArray($import, $filePath, $readerType, $disk);
    }

    /**
     * @return Collection<array-key, Collection<int, Collection<array-key, mixed>>>
     */
    public function toCollection(?Import $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): Collection
    {
        $readerType = FileTypeDetector::detect($filePath, $readerType);

        return $this->reader->toCollection($import, $filePath, $readerType, $disk);
    }

    public function queueImport(ShouldQueue&Import $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): PendingDispatch|PendingBatch
    {
        $response = $this->import($import, $filePath, $disk, $readerType);

        // A ShouldQueue import always yields a pending dispatch or batch; the
        // reader throws before reaching this point when it cannot be queued.
        assert($response instanceof PendingDispatch || $response instanceof PendingBatch);

        return $response;
    }

    /**
     * Register a custom export source handler.
     *
     * The handler may implement SheetSourceHandler (sync), QueuedSheetSourceHandler
     * (queued), or both. Pass a class name to have it resolved lazily from the
     * service container, or pass an instance directly.
     *
     * @param  SheetSourceHandler|QueuedSheetSourceHandler|class-string  ...$handlers
     */
    public function registerSourceHandler(SheetSourceHandler|QueuedSheetSourceHandler|string ...$handlers): void
    {
        $this->handlerRegistry->register(...$handlers);
    }

    /**
     * @throws Exception
     */
    protected function export(Export $export, string $fileName, ?string $writerType = null): TemporaryFile
    {
        $writerType = FileTypeDetector::detectStrict($fileName, $writerType);

        return $this->writer->export($export, $writerType);
    }
}
