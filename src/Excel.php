<?php

namespace Maatwebsite\Excel;

use Illuminate\Filesystem\FilesystemManager;
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

    /**
     * @var Writer
     */
    protected $writer;

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
     * @param ResponseFactory   $response
     * @param FilesystemManager $filesystem
     */
    public function __construct(Writer $writer, ResponseFactory $response, FilesystemManager $filesystem)
    {
        $this->writer     = $writer;
        $this->response   = $response;
        $this->filesystem = $filesystem;
    }

    /**
     * @param object      $export
     * @param string      $writerType
     * @param string|null $fileName
     *
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
     * @return bool
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        $file = $this->export($export, $filePath, $writerType);

        return $this->filesystem->disk($disk)->put($filePath, file_get_contents($file));
    }

    /**
     * @param object      $export
     * @param string      $writerType
     * @param string|null $fileName
     *
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
        if (!isset($pathinfo['extension'])) {
            return null;
        }

        switch (strtolower($pathinfo['extension'])) {
            case 'xlsx': // Excel (OfficeOpenXML) Spreadsheet
            case 'xlsm': // Excel (OfficeOpenXML) Macro Spreadsheet (macros will be discarded)
            case 'xltx': // Excel (OfficeOpenXML) Template
            case 'xltm': // Excel (OfficeOpenXML) Macro Template (macros will be discarded)
                return self::XLSX;
            case 'xls': // Excel (BIFF) Spreadsheet
            case 'xlt': // Excel (BIFF) Template
                return self::XLS;
            case 'ods': // Open/Libre Offic Calc
            case 'ots': // Open/Libre Offic Calc Template
                return self::ODS;
            case 'slk':
                return self::SLK;
            case 'xml': // Excel 2003 SpreadSheetML
                return self::XML;
            case 'gnumeric':
                return self::GNUMERIC;
            case 'htm':
            case 'html':
                return self::HTML;
            case 'csv':
                return self::CSV;
            default:
                return null;
        }
    }
}
