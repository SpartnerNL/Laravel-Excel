<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Foundation\Bus\PendingDispatch;
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
     * @var Factory
     */
    protected $filesystem;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Writer            $writer
     * @param QueuedWriter      $queuedWriter
     * @param Reader            $reader
     * @param Factory $filesystem
     */
    public function __construct(
        Writer $writer,
        QueuedWriter $queuedWriter,
        Reader $reader,
        Factory $filesystem
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
        $file = $this->export($export, $fileName, $writerType);

        return response()->download($file, $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $disk, $writerType);
        }

        $file = $this->export($export, $filePath, $writerType);

        return $this->filesystem->disk($disk)->put($filePath, fopen($file, 'r+'));
    }

    /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null)
    {
        $writerType = $this->findTypeByExtension($filePath, $writerType);

        return $this->queuedWriter->store($export, $filePath, $disk, $writerType);
    }

    /**
     * {@inheritdoc}
     */
    public function import($import, $filePath, string $disk = null, string $readerType = null)
    {
        $readerType = $this->getReaderType($filePath, $readerType);

        $response =  $this->reader->read($import, $filePath, $readerType, $disk);

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
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return string
     */
    protected function export($export, string $fileName, string $writerType = null)
    {
        $writerType = $this->findTypeByExtension($fileName, $writerType);

        return $this->writer->export($export, $writerType);
    }

    /**
     * @param string|UploadedFile $fileName
     * @param string|null         $type
     *
     * @return string|null
     */
    protected function findTypeByExtension($fileName, string $type = null): string
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
     * @return string
     */
    private function getReaderType($filePath, string $readerType = null): string
    {
        $readerType = $this->findTypeByExtension($filePath, $readerType);
        $readerType = $readerType ?? IOFactory::identify($filePath);

        if (null === $readerType) {
            throw new NoTypeDetectedException();
        }

        return $readerType;
    }
}
