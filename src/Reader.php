<?php

namespace Maatwebsite\Excel;

use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Maatwebsite\Excel\Events\AfterImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Filesystem\Factory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Maatwebsite\Excel\Factories\ReaderFactory;
use Maatwebsite\Excel\Concerns\MapsCsvSettings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
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
     * @var object[]
     */
    protected $sheetImports = [];

    /**
     * @var string
     */
    protected $currentFile;

    /**
     * @var Factory
     */
    private $filesystem;

    /**
     * @param Factory $filesystem
     */
    public function __construct(Factory $filesystem)
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
     * @throws Exceptions\UnreadableFileException
     * @throws InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @return \Illuminate\Foundation\Bus\PendingDispatch|$this
     */
    public function read($import, $filePath, string $readerType, string $disk = null)
    {
        $reader = $this->getReader($import, $filePath, $readerType, $disk);

        if ($import instanceof WithChunkReading) {
            return (new ChunkReader)->read($import, $reader, $this->currentFile);
        }

        $this->beforeReading($import, $reader);

        DB::transaction(function () {
            foreach ($this->sheetImports as $index => $sheetImport) {
                $sheet = Sheet::make($this->spreadsheet, $index);
                $sheet->import($sheetImport, $sheet->getStartRow($sheetImport));
                $sheet->disconnect();
            }
        });

        $this->afterReading($import);

        return $this;
    }

    /**
     * @param object              $import
     * @param string|UploadedFile $filePath
     * @param string              $readerType
     * @param string|null         $disk
     *
     * @throws Exceptions\UnreadableFileException
     * @throws InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @return array
     */
    public function toArray($import, $filePath, string $readerType, string $disk = null): array
    {
        $reader = $this->getReader($import, $filePath, $readerType, $disk);
        $this->beforeReading($import, $reader);

        $sheets = [];
        foreach ($this->sheetImports as $index => $sheetImport) {
            $sheet          = Sheet::make($this->spreadsheet, $index);
            $sheets[$index] = $sheet->toArray($sheetImport, $sheet->getStartRow($sheetImport));
            $sheet->disconnect();
        }

        $this->afterReading($import);

        return $sheets;
    }

    /**
     * @param object              $import
     * @param string|UploadedFile $filePath
     * @param string              $readerType
     * @param string|null         $disk
     *
     * @throws Exceptions\UnreadableFileException
     * @throws InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @return Collection
     */
    public function toCollection($import, $filePath, string $readerType, string $disk = null): Collection
    {
        $reader = $this->getReader($import, $filePath, $readerType, $disk);
        $this->beforeReading($import, $reader);

        $sheets = new Collection();
        foreach ($this->sheetImports as $index => $sheetImport) {
            $sheet = Sheet::make($this->spreadsheet, $index);
            $sheets->put($index, $sheet->toCollection($sheetImport, $sheet->getStartRow($sheetImport)));
            $sheet->disconnect();
        }

        $this->afterReading($import);

        return $sheets;
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
     * Garbage collect.
     */
    private function garbageCollect()
    {
        $this->setDefaultValueBinder();

        // Force garbage collecting
        unset($this->sheetImports, $this->spreadsheet);

        // Remove the temporary file.
        unlink($this->currentFile);
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

    /**
     * @param object              $import
     * @param string|UploadedFile $filePath
     * @param string              $readerType
     * @param string              $disk
     *
     * @throws Exceptions\UnreadableFileException
     * @throws InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return IReader
     */
    private function getReader($import, $filePath, string $readerType, string $disk = null): IReader
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

        $this->currentFile = $this->copyToFileSystem($filePath, $disk);

        $reader = ReaderFactory::make($this->currentFile, $readerType);

        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(config('excel.imports.read_only', true));
        }

        if ($reader instanceof Csv) {
            $reader->setDelimiter($this->delimiter);
            $reader->setEnclosure($this->enclosure);
            $reader->setEscapeCharacter($this->escapeCharacter);
            $reader->setContiguous($this->contiguous);
            $reader->setInputEncoding($this->inputEncoding);
        }

        return $reader;
    }

    /**
     * @param object  $import
     * @param IReader $reader
     */
    private function beforeReading($import, IReader $reader)
    {
        $this->sheetImports = $this->buildSheetImports($import, $reader);

        $this->spreadsheet = $reader->load($this->currentFile);

        // When no multiple sheets, use the main import object
        // for each loaded sheet in the spreadsheet
        if (!$import instanceof WithMultipleSheets) {
            $this->sheetImports = array_fill(0, $this->spreadsheet->getSheetCount(), $import);
        }

        $this->raise(new BeforeImport($this, $import));
    }

    /**
     * @param object $import
     */
    private function afterReading($import)
    {
        $this->raise(new AfterImport($this, $import));
        $this->garbageCollect();
    }
}
