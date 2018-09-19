<?php

namespace Maatwebsite\Excel;

use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemManager;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Maatwebsite\Excel\Factories\ReaderFactory;
use Maatwebsite\Excel\Concerns\MapsCsvSettings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Reader
{
    use DelegatedMacroable, HasEventBus, MapsCsvSettings;

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var string
     */
    protected $tmpPath;

    /**
     * @var FilesystemManager
     */
    private $filesystem;

    /**
     * @param FilesystemManager $filesystem
     */
    public function __construct(FilesystemManager $filesystem)
    {
        $this->filesystem = $filesystem;

        $this->tmpPath = config('excel.exports.temp_path', sys_get_temp_dir());
        $this->applyCsvSettings(config('excel.exports.csv', []));

        $this->setDefaultValueBinder();
    }

    /**
     * @param object              $import
     * @param string|UploadedFile $filePath
     * @param string              $readerType
     * @param string|null         $disk
     *
     * @return \Illuminate\Foundation\Bus\PendingDispatch|null
     */
    public function read($import, $filePath, string $readerType, string $disk = null)
    {
        if ($import instanceof ShouldQueue && !$import instanceof WithChunkReading) {
            throw new InvalidArgumentException('ShouldQueue is only supported in combination with WithChunkReading.');
        }

        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        if ($import instanceof WithCustomValueBinder) {
            Cell::setValueBinder($import);
        }

        if ($import instanceof WithCustomCsvSettings) {
            $this->applyCsvSettings($import->getCsvSettings());
        }

        $file = $this->copyToFileSystem($filePath, $disk);

        $reader = ReaderFactory::make($file, $readerType);

        if ($reader instanceof Csv) {
            $reader->setDelimiter($this->delimiter);
            $reader->setEnclosure($this->enclosure);
            $reader->setEscapeCharacter($this->escapeCharacter);
            $reader->setContiguous($this->contiguous);
            $reader->setInputEncoding($this->inputEncoding);
        }

        $this->raise(new BeforeImport($this, $import));

        if ($import instanceof WithChunkReading) {
            return (new ChunkReader)->read($import, $reader, $file);
        }

        $sheetImports = $this->buildSheetImports($import, $reader);

        $this->spreadsheet = $reader->load($file);

        // When no multiple sheets, use the main import object
        // for each loaded sheet in the spreadsheet
        if (!$import instanceof WithMultipleSheets) {
            $sheetImports = array_fill(0, $this->spreadsheet->getSheetCount(), $import);
        }

        foreach ($sheetImports as $index => $sheetImport) {
            $sheet    = Sheet::make($this->spreadsheet, $index);
            $startRow = HeadingRowExtractor::determineStartRow($sheetImport);
            $sheet->import($sheetImport, $startRow);
            $sheet->disconnect();
        }

        $this->setDefaultValueBinder();

        // Force garbage collecting
        unset($sheetImports, $this->spreadsheet);

        // Remove the temporary file.
        unlink($file);

        return null;
    }

    /**
     * @return object
     */
    public function getDelegate()
    {
        return $this->spreadsheet;
    }

    /**
     * @return $this
     */
    public function setDefaultValueBinder()
    {
        Cell::setValueBinder(new DefaultValueBinder);

        return $this;
    }

    /**
     * @param UploadedFile|string $filePath
     * @param string|null         $disk
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return string
     */
    protected function copyToFileSystem($filePath, string $disk = null)
    {
        $tempFilePath = $this->getTmpFile();

        if ($filePath instanceof UploadedFile) {
            return $filePath->move($tempFilePath)->getRealPath();
        }

        $tmpStream = fopen($tempFilePath, 'w+');

        $file = $this->filesystem->disk($disk)->readStream($filePath);

        stream_copy_to_stream($file, $tmpStream);
        fclose($tmpStream);

        return $tempFilePath;
    }

    /**
     * @return string
     */
    protected function getTmpFile(): string
    {
        return $this->tmpPath . DIRECTORY_SEPARATOR . str_random(16);
    }

    /**
     * @param object  $import
     * @param IReader $reader
     *
     * @return array
     */
    private function buildSheetImports($import, IReader $reader): array
    {
        $sheetImports = [];
        if ($import instanceof WithMultipleSheets) {
            $sheetImports = $import->sheets();

            if (method_exists($reader, 'setLoadSheetsOnly')) {
                $reader->setLoadSheetsOnly(array_keys($sheetImports));
            }
        }

        return $sheetImports;
    }
}
