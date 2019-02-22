<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Files\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;

class Excel implements Exporter, Importer
{
    use RegistersCustomConcerns;

    const XLSX     = 'Xlsx';

    const CSV      = 'Csv';

    const TSV      = 'Csv';

    const ODS      = 'Ods';

    const XLS      = 'Xls';

    const SLK      = 'Slk';

    const XML      = 'Xml';

    const GNUMERIC = 'Gnumeric';

    const HTML     = 'Html';

    const MPDF     = 'Mpdf';

    const DOMPDF   = 'Dompdf';

    const TCPDF    = 'Tcpdf';

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var QueuedWriter
     */
    protected $queuedWriter;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Writer       $writer
     * @param QueuedWriter $queuedWriter
     * @param Reader       $reader
     * @param Filesystem   $filesystem
     */
    public function __construct(
        Writer $writer,
        QueuedWriter $queuedWriter,
        Reader $reader,
        Filesystem $filesystem
    ) {
        $this->writer       = $writer;
        $this->reader       = $reader;
        $this->filesystem   = $filesystem;
        $this->queuedWriter = $queuedWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function download($export, string $fileName, string $writerType = null)
    {
        return response()->download(
            $this->export($export, $fileName, $writerType),
            $fileName
        );
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $diskName = null, string $writerType = null, $diskOptions = [])
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $diskName, $writerType, $diskOptions);
        }

        return $this->filesystem->disk($diskName, $diskOptions)->put(
            $this->export($export, $filePath, $writerType),
            $filePath
        );
    }

    /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $diskName = null, string $writerType = null, $diskOptions = [])
    {
        $writerType = $this->findTypeByExtension($filePath, $writerType);

        if (!$writerType) {
            throw new NoTypeDetectedException();
        }

        $disk = $this->filesystem->disk($diskName, $diskOptions);

        return $this->queuedWriter->store(
            $export,
            $filePath,
            $disk,
            $writerType
        );
    }

    /**
     * {@inheritdoc}
     */
    public function import($import, $filePath, string $disk = null, string $readerType = null)
    {
        $readerType = $this->getReaderType($filePath, $readerType);
        $response   = $this->reader->read($import, $filePath, $readerType, $disk);

        if ($response instanceof PendingDispatch) {
            return $response;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($import, $filePath, string $disk = null, string $readerType = null): array
    {
        $readerType = $this->getReaderType($filePath, $readerType);

        return $this->reader->toArray($import, $filePath, $readerType, $disk);
    }

    /**
     * {@inheritdoc}
     */
    public function toCollection($import, $filePath, string $disk = null, string $readerType = null): Collection
    {
        $readerType = $this->getReaderType($filePath, $readerType);

        return $this->reader->toCollection($import, $filePath, $readerType, $disk);
    }

    /**
     * {@inheritdoc}
     */
    public function queueImport(ShouldQueue $import, $filePath, string $disk = null, string $readerType = null)
    {
        return $this->import($import, $filePath, $disk, $readerType);
    }

    /**
     * @param object      $export
     * @param string|null $fileName
     * @param string      $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return string
     */
    protected function export($export, string $fileName, string $writerType = null): string
    {
        $writerType = $this->findTypeByExtension($fileName, $writerType);

        if (!$writerType) {
            throw new NoTypeDetectedException();
        }

        return $this->writer->export($export, $writerType);
    }

    /**
     * @param string|UploadedFile $fileName
     * @param string|null         $type
     *
     * @return string|null
     */
    protected function findTypeByExtension($fileName, string $type = null)
    {
        if (null !== $type) {
            return $type;
        }

        if (!$fileName instanceof UploadedFile) {
            $pathInfo  = pathinfo($fileName);
            $extension = $pathInfo['extension'] ?? '';
        } else {
            $extension = $fileName->getClientOriginalExtension();
        }

        if (null === $type && trim($extension) === '') {
            throw new NoTypeDetectedException();
        }

        return config('excel.extension_detector.' . strtolower($extension));
    }

    /**
     * @param string|UploadedFile $filePath
     * @param string|null         $readerType
     *
     * @throws NoTypeDetectedException
     * @return string|null
     */
    private function getReaderType($filePath, string $readerType = null)
    {
        return $this->findTypeByExtension($filePath, $readerType);
    }
}
