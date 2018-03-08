<?php

namespace Maatwebsite\Excel;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Contracts\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Excel
{
    const XLSX     = 'Xlsx';

    const CSV      = 'Csv';

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
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var FilesystemManager
     */
    protected $filesystem;

    /**
     * @param Writer            $writer
     * @param QueuedWriter      $queuedWriter
     * @param ResponseFactory   $response
     * @param FilesystemManager $filesystem
     */
    public function __construct(
        Writer $writer,
        QueuedWriter $queuedWriter,
        ResponseFactory $response,
        FilesystemManager $filesystem
    ) {
        $this->writer       = $writer;
        $this->response     = $response;
        $this->filesystem   = $filesystem;
        $this->queuedWriter = $queuedWriter;
    }

    /**
     * @param object      $export
     * @param string|null $fileName
     * @param string      $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return BinaryFileResponse
     */
    public function download($export, string $fileName, string $writerType = null)
    {
        $file = $this->export($export, $fileName, $writerType);

        return $this->response->download($file, $fileName);
    }

    /**
     * @param object      $export
     * @param string      $filePath
     * @param string|null $disk
     * @param string      $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return bool
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        $file = $this->export($export, $filePath, $writerType);

        return $this->filesystem->disk($disk)->put($filePath, fopen($file, 'r+'));
    }

    /**
     * @param object      $export
     * @param string      $filePath
     * @param string|null $disk
     * @param string      $writerType
     *
     * @return PendingDispatch
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null)
    {
        if (null === $writerType) {
            $writerType = $this->findTypeByExtension($filePath);
        }

        return $this->queuedWriter->store($export, $filePath, $disk, $writerType);
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
        if (null === $writerType) {
            $writerType = $this->findTypeByExtension($fileName);
        }

        return $this->writer->export($export, $writerType);
    }

    /**
     * @param string $fileName
     *
     * @return string|null
     */
    protected function findTypeByExtension(string $fileName)
    {
        $pathinfo = pathinfo($fileName);

        return config('excel.extension_detector.' . strtolower($pathinfo['extension'] ?? ''));
    }

    /**
     * Get the writer.
     *
     * @return Writer
     */
    public function getWritter()
    {
        return $this->writer;
    }

    /**
     * Get the queued writer.
     *
     * @return QueuedWriter
     */
    public function getQueuedWritter()
    {
        return $this->queuedWriter;
    }
}
