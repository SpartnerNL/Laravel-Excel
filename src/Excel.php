<?php

namespace Maatwebsite\Excel;

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
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

    public const XLSX = 'Xlsx';

    public const CSV = 'Csv';

    public const TSV = 'Csv';

    public const ODS = 'Ods';

    public const XLS = 'Xls';

    public const SLK = 'Slk';

    public const XML = 'Xml';

    public const GNUMERIC = 'Gnumeric';

    public const HTML = 'Html';

    public const MPDF = 'Mpdf';

    public const DOMPDF = 'Dompdf';

    public const TCPDF = 'Tcpdf';

    public function __construct(
        protected Writer $writer,
        protected QueuedWriter $queuedWriter,
        private Reader $reader,
        protected Filesystem $filesystem,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param  array<string, string>  $headers
     */
    public function download(object $export, string $fileName, ?string $writerType = null, array $headers = []): BinaryFileResponse
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
    public function store(object $export, string $filePath, ?string $diskName = null, ?string $writerType = null, mixed $diskOptions = [], ?string $disk = null): bool|PendingDispatch|PendingBatch
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $diskName ?: $disk, $writerType, $diskOptions);
        }

        $temporaryFile = $this->export($export, $filePath, $writerType);

        $exported = $this->filesystem->disk($diskName ?: $disk, $diskOptions)->copy(
            $temporaryFile,
            $filePath
        );

        $temporaryFile->delete();

        return $exported;
    }

    public function queue(object $export, string $filePath, ?string $disk = null, ?string $writerType = null, mixed $diskOptions = []): PendingDispatch|PendingBatch
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

    public function raw(object $export, string $writerType): string
    {
        $temporaryFile = $this->writer->export($export, $writerType);

        $contents = $temporaryFile->contents();
        $temporaryFile->delete();

        return $contents;
    }

    /**
     * @throws ValidationException
     */
    public function import(object $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): static|PendingDispatch|PendingBatch
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
    public function toArray(object $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): array
    {
        $readerType = FileTypeDetector::detect($filePath, $readerType);

        return $this->reader->toArray($import, $filePath, $readerType, $disk);
    }

    /**
     * @return Collection<array-key, Collection<int, Collection<array-key, mixed>>>
     */
    public function toCollection(?object $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): Collection
    {
        $readerType = FileTypeDetector::detect($filePath, $readerType);

        return $this->reader->toCollection($import, $filePath, $readerType, $disk);
    }

    public function queueImport(ShouldQueue $import, string|UploadedFile $filePath, ?string $disk = null, ?string $readerType = null): PendingDispatch|PendingBatch
    {
        $response = $this->import($import, $filePath, $disk, $readerType);

        // A ShouldQueue import always yields a pending dispatch or batch; the
        // reader throws before reaching this point when it cannot be queued.
        assert($response instanceof PendingDispatch || $response instanceof PendingBatch);

        return $response;
    }

    /**
     * @throws Exception
     */
    protected function export(object $export, string $fileName, ?string $writerType = null): TemporaryFile
    {
        $writerType = FileTypeDetector::detectStrict($fileName, $writerType);

        return $this->writer->export($export, $writerType);
    }
}
