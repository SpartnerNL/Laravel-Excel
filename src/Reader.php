<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Filesystem\FilesystemManager;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Maatwebsite\Excel\Concerns\MapsCsvSettings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Exceptions\UnreadableFileException;

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
     * @param object      $import
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $readerType
     *
     * @return bool
     */
    public function read($import, string $filePath, string $disk = null, string $readerType = null)
    {
        if ($import instanceof WithCustomValueBinder) {
            Cell::setValueBinder($import);
        }

        if ($import instanceof WithCustomCsvSettings) {
            $this->applyCsvSettings($import->getCsvSettings());
        }

        $file = $this->copyToFileSystem($filePath, $disk);

        $reader = $this->getReader($file, $readerType);

        if ($reader instanceof Csv) {
            $reader->setDelimiter($this->delimiter);
            $reader->setEnclosure($this->enclosure);
            $reader->setEscapeCharacter($this->escapeCharacter);
            $reader->setContiguous($this->contiguous);
            $reader->setInputEncoding($this->inputEncoding);
        }

        $this->spreadsheet = $reader->load($file);

        $sheetExports = array_fill(0, $this->spreadsheet->getSheetCount(), $import);
        if ($import instanceof WithMultipleSheets) {
            $sheetExports = $import->sheets();
        }

        foreach ($sheetExports as $index => $sheetExport) {
            $this->loadSheet($index)->import($sheetExport);
        }

        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);

        return $this;
    }

    /**
     * @param null $nullValue
     * @param bool $calculateFormulas
     * @param bool $formatData
     * @param bool $returnCellRef
     *
     * @return array
     */
    public function toArray($nullValue = null, $calculateFormulas = false, $formatData = false, $returnCellRef = false)
    {
        $sheets = [];
        foreach ($this->spreadsheet->getAllSheets() as $sheet) {
            $sheets[] = (new Sheet($sheet))->toArray($nullValue, $calculateFormulas, $formatData, $returnCellRef);
        }

        return $sheets;
    }

    /**
     * @param null $nullValue
     * @param bool $calculateFormulas
     * @param bool $formatData
     * @param bool $returnCellRef
     *
     * @return Collection
     */
    public function toCollection($nullValue = null, $calculateFormulas = false, $formatData = false, $returnCellRef = false): Collection
    {
        $sheets = new Collection();
        foreach ($this->spreadsheet->getAllSheets() as $sheet) {
            $sheets->push((new Sheet($sheet))->toCollection($nullValue, $calculateFormulas, $formatData, $returnCellRef));
        }

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
     * @param string      $filePath
     * @param string|null $disk
     *
     * @return string
     */
    protected function copyToFileSystem(string $filePath, string $disk = null)
    {
        $tempFilePath = $this->getTmpFile($filePath);
        $tmpStream    = fopen($tempFilePath, 'w+');

        $file = $this->filesystem->disk($disk)->readStream($filePath);

        stream_copy_to_stream($file, $tmpStream);
        fclose($tmpStream);

        return $tempFilePath;
    }

    /**
     * @param string|null $readerType
     * @param string      $tmp
     *
     * @return IReader
     */
    protected function getReader(string $filePath, string $readerType = null): IReader
    {
        $readerType = $readerType ?? IOFactory::identify($filePath);
        $reader     = IOFactory::createReader($readerType);

        if (!$reader->canRead($filePath)) {
            throw new UnreadableFileException;
        }

        return $reader;
    }

    /**
     * @param string $filePath
     *
     * @return string
     */
    protected function getTmpFile(string $filePath): string
    {
        $tmp = $this->tmpPath . DIRECTORY_SEPARATOR . str_random(16) . '.' . pathinfo($filePath)['extension'];

        return $tmp;
    }

    /**
     * @param string|int $index
     *
     * @return Sheet
     */
    private function loadSheet($index): Sheet
    {
        if (is_numeric($index)) {
            $sheet = $this->spreadsheet->getSheet($index);
        } else {
            $sheet = $this->spreadsheet->getSheetByName($index);
        }

        return new Sheet($sheet);
    }
}
