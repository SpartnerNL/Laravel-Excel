<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Contracts\Support\Arrayable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Imports\EndRowFinder;
use Maatwebsite\Excel\Concerns\FromIterator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Imports\ModelImporter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Exceptions\ConcernConflictException;
use PhpOffice\PhpSpreadsheet\Cell\Cell as SpreadsheetCell;

class Sheet
{
    use DelegatedMacroable, HasEventBus;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @var string
     */
    protected $tmpPath;

    /**
     * @var object
     */
    protected $exportable;

    /**
     * @var Worksheet
     */
    private $worksheet;

    /**
     * @param Worksheet $worksheet
     */
    public function __construct(Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
        $this->chunkSize = config('excel.exports.chunk_size', 100);
        $this->tmpPath   = config('excel.exports.temp_path', sys_get_temp_dir());
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param string|int  $index
     *
     * @return Sheet
     */
    public static function make(Spreadsheet $spreadsheet, $index)
    {
        if (is_numeric($index)) {
            return Sheet::byIndex($spreadsheet, $index);
        }

        return Sheet::byName($spreadsheet, $index);
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param int         $index
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return Sheet
     */
    public static function byIndex(Spreadsheet $spreadsheet, int $index)
    {
        return new static($spreadsheet->getSheet($index));
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param string      $name
     *
     * @return Sheet
     */
    public static function byName(Spreadsheet $spreadsheet, string $name)
    {
        return new static($spreadsheet->getSheetByName($name));
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function open($sheetExport)
    {
        $this->exportable = $sheetExport;

        if ($sheetExport instanceof WithCustomValueBinder) {
            SpreadsheetCell::setValueBinder($sheetExport);
        }

        if ($sheetExport instanceof WithEvents) {
            $this->registerListeners($sheetExport->registerEvents());
        }

        $this->raise(new BeforeSheet($this, $this->exportable));

        if ($sheetExport instanceof WithTitle) {
            $this->worksheet->setTitle($sheetExport->title());
        }

        if (($sheetExport instanceof FromQuery || $sheetExport instanceof FromCollection || $sheetExport instanceof FromArray) && $sheetExport instanceof FromView) {
            throw ConcernConflictException::queryOrCollectionAndView();
        }

        if (!$sheetExport instanceof FromView && $sheetExport instanceof WithHeadings) {
            if ($sheetExport instanceof WithCustomStartCell) {
                $startCell = $sheetExport->startCell();
            }

            $this->append([$sheetExport->headings()], $startCell ?? null, $this->hasStrictNullComparison($sheetExport));
        }

        if ($sheetExport instanceof WithCharts) {
            $this->addCharts($sheetExport->charts());
        }

        if ($sheetExport instanceof WithDrawings) {
            $this->addDrawings($sheetExport->drawings());
        }
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function export($sheetExport)
    {
        $this->open($sheetExport);

        if ($sheetExport instanceof FromView) {
            $this->fromView($sheetExport);
        } else {
            if ($sheetExport instanceof FromQuery) {
                $this->fromQuery($sheetExport, $this->worksheet);
            }

            if ($sheetExport instanceof FromCollection) {
                $this->fromCollection($sheetExport);
            }

            if ($sheetExport instanceof FromArray) {
                $this->fromArray($sheetExport);
            }

            if ($sheetExport instanceof FromIterator) {
                $this->fromIterator($sheetExport);
            }
        }

        $this->close($sheetExport);
    }

    /**
     * @param object $import
     * @param int    $startRow
     */
    public function import($import, int $startRow = 1)
    {
        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        $this->raise(new BeforeSheet($this, $this->exportable));

        if ($import instanceof WithProgressBar && !$import instanceof WithChunkReading) {
            $import->getConsoleOutput()->progressStart($this->worksheet->getHighestRow());
        }

        $calculatesFormulas = $import instanceof WithCalculatedFormulas;

        if ($import instanceof WithMappedCells) {
            app(MappedReader::class)->map($import, $this->worksheet);
        } else {
            if ($import instanceof ToModel) {
                app(ModelImporter::class)->import($this->worksheet, $import, $startRow);
            }

            if ($import instanceof ToCollection) {
                $import->collection($this->toCollection($import, $startRow, null, $calculatesFormulas));
            }

            if ($import instanceof ToArray) {
                $import->array($this->toArray($import, $startRow, null, $calculatesFormulas));
            }
        }

        if ($import instanceof OnEachRow) {
            $headingRow = HeadingRowExtractor::extract($this->worksheet, $import);
            foreach ($this->worksheet->getRowIterator()->resetStart($startRow ?? 1) as $row) {
                $import->onRow(new Row($row, $headingRow));

                if ($import instanceof WithProgressBar) {
                    $import->getConsoleOutput()->progressAdvance();
                }
            }
        }

        $this->raise(new AfterSheet($this, $this->exportable));

        if ($import instanceof WithProgressBar && !$import instanceof WithChunkReading) {
            $import->getConsoleOutput()->progressFinish();
        }
    }

    /**
     * @param object   $import
     * @param int|null $startRow
     * @param null     $nullValue
     * @param bool     $calculateFormulas
     * @param bool     $formatData
     *
     * @return array
     */
    public function toArray($import, int $startRow = null, $nullValue = null, $calculateFormulas = false, $formatData = false)
    {
        $endRow     = EndRowFinder::find($import, $startRow);
        $headingRow = HeadingRowExtractor::extract($this->worksheet, $import);

        $rows = [];
        foreach ($this->worksheet->getRowIterator($startRow, $endRow) as $row) {
            $row = (new Row($row, $headingRow))->toArray($nullValue, $calculateFormulas, $formatData);

            if ($import instanceof WithMapping) {
                $row = $import->map($row);
            }

            $rows[] = $row;

            if ($import instanceof WithProgressBar) {
                $import->getConsoleOutput()->progressAdvance();
            }
        }

        return $rows;
    }

    /**
     * @param object   $import
     * @param int|null $startRow
     * @param null     $nullValue
     * @param bool     $calculateFormulas
     * @param bool     $formatData
     *
     * @return Collection
     */
    public function toCollection($import, int $startRow = null, $nullValue = null, $calculateFormulas = false, $formatData = false): Collection
    {
        return new Collection(array_map(function (array $row) {
            return new Collection($row);
        }, $this->toArray($import, $startRow, $nullValue, $calculateFormulas, $formatData)));
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function close($sheetExport)
    {
        $this->exportable = $sheetExport;

        if ($sheetExport instanceof WithColumnFormatting) {
            foreach ($sheetExport->columnFormats() as $column => $format) {
                $this->formatColumn($column, $format);
            }
        }

        if ($sheetExport instanceof ShouldAutoSize) {
            $this->autoSize();
        }

        $this->raise(new AfterSheet($this, $this->exportable));
    }

    /**
     * @param FromView $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function fromView(FromView $sheetExport)
    {
        $tempFile = $this->tempFile();
        file_put_contents($tempFile, $sheetExport->view()->render());

        $spreadsheet = $this->worksheet->getParent();

        /** @var Html $reader */
        $reader = IOFactory::createReader('Html');

        // Insert content into the last sheet
        $reader->setSheetIndex($spreadsheet->getSheetCount() - 1);
        $reader->loadIntoExisting($tempFile, $spreadsheet);
    }

    /**
     * @param FromQuery $sheetExport
     * @param Worksheet $worksheet
     */
    public function fromQuery(FromQuery $sheetExport, Worksheet $worksheet)
    {
        $sheetExport->query()->chunk($this->getChunkSize($sheetExport), function ($chunk) use ($sheetExport, $worksheet) {
            foreach ($chunk as $row) {
                $this->appendRow($row, $sheetExport);
            }
        });
    }

    /**
     * @param FromCollection $sheetExport
     */
    public function fromCollection(FromCollection $sheetExport)
    {
        $this->appendRows($sheetExport->collection()->all(), $sheetExport);
    }

    /**
     * @param FromArray $sheetExport
     */
    public function fromArray(FromArray $sheetExport)
    {
        $this->appendRows($sheetExport->array(), $sheetExport);
    }

    /**
     * @param FromIterator $sheetExport
     */
    public function fromIterator(FromIterator $sheetExport)
    {
        $this->appendRows($sheetExport->iterator(), $sheetExport);
    }

    /**
     * @param array       $rows
     * @param string|null $startCell
     * @param bool        $strictNullComparison
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function append(array $rows, string $startCell = null, bool $strictNullComparison = false)
    {
        if (!$startCell) {
            $startCell = 'A1';
        }

        if ($this->hasRows()) {
            $startCell = 'A' . ($this->worksheet->getHighestRow() + 1);
        }

        $this->worksheet->fromArray($rows, null, $startCell, $strictNullComparison);
    }

    /**
     * @return void
     */
    public function autoSize()
    {
        foreach ($this->buildColumnRange('A', $this->worksheet->getHighestDataColumn()) as $col) {
            $this->worksheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * @param string $column
     * @param string $format
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function formatColumn(string $column, string $format)
    {
        $this->worksheet
            ->getStyle($column . '1:' . $column . $this->worksheet->getHighestRow())
            ->getNumberFormat()
            ->setFormatCode($format);
    }

    /**
     * @param int $chunkSize
     *
     * @return Sheet
     */
    public function chunkSize(int $chunkSize)
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * @return Worksheet
     */
    public function getDelegate()
    {
        return $this->worksheet;
    }

    /**
     * @param Chart|Chart[] $charts
     */
    public function addCharts($charts)
    {
        $charts = \is_array($charts) ? $charts : [$charts];

        foreach ($charts as $chart) {
            $this->worksheet->addChart($chart);
        }
    }

    /**
     * @param BaseDrawing|BaseDrawing[] $drawings
     */
    public function addDrawings($drawings)
    {
        $drawings = \is_array($drawings) ? $drawings : [$drawings];

        foreach ($drawings as $drawing) {
            $drawing->setWorksheet($this->worksheet);
        }
    }

    /**
     * @param string $concern
     *
     * @return string
     */
    public function hasConcern(string $concern): string
    {
        return $this->exportable instanceof $concern;
    }

    /**
     * @param iterable $rows
     * @param object   $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function appendRows($rows, $sheetExport)
    {
        $append = [];
        foreach ($rows as $row) {
            if ($sheetExport instanceof WithMapping) {
                $row = $sheetExport->map($row);
            }

            $append[] = static::mapArraybleRow($row);
        }

        if ($sheetExport instanceof WithCustomStartCell) {
            $startCell = $sheetExport->startCell();
        }

        $this->append($append, $startCell ?? null, $this->hasStrictNullComparison($sheetExport));
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public static function mapArraybleRow($row): array
    {
        // When dealing with eloquent models, we'll skip the relations
        // as we won't be able to display them anyway.
        if (method_exists($row, 'attributesToArray')) {
            return $row->attributesToArray();
        }

        if ($row instanceof Arrayable) {
            return $row->toArray();
        }

        // Convert StdObjects to arrays
        if (is_object($row)) {
            return json_decode(json_encode($row), true);
        }

        return $row;
    }

    /**
     * @param $sheetImport
     *
     * @return int
     */
    public function getStartRow($sheetImport): int
    {
        return HeadingRowExtractor::determineStartRow($sheetImport);
    }

    /**
     * Disconnect the sheet.
     */
    public function disconnect()
    {
        $this->worksheet->disconnectCells();
        unset($this->worksheet);
    }

    /**
     * @param iterable $row
     * @param object   $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function appendRow($row, $sheetExport)
    {
        if ($sheetExport instanceof WithMapping) {
            $row = $sheetExport->map($row);
        }

        $row = static::mapArraybleRow($row);

        if ($sheetExport instanceof WithCustomStartCell) {
            $startCell = $sheetExport->startCell();
        }

        if (isset($row[0]) && is_array($row[0])) {
            $this->append($row, $startCell ?? null, $this->hasStrictNullComparison($sheetExport));
        } else {
            $this->append([$row], $startCell ?? null, $this->hasStrictNullComparison($sheetExport));
        }
    }

    /**
     * @param string $lower
     * @param string $upper
     *
     * @return \Generator
     */
    protected function buildColumnRange(string $lower, string $upper)
    {
        $upper++;
        for ($i = $lower; $i !== $upper; $i++) {
            yield $i;
        }
    }

    /**
     * @return string
     */
    protected function tempFile(): string
    {
        return $this->tmpPath . DIRECTORY_SEPARATOR . 'laravel-excel-' . str_random(16);
    }

    /**
     * @return bool
     */
    private function hasRows(): bool
    {
        return $this->worksheet->cellExists('A1');
    }

    /**
     * @param object $sheetExport
     *
     * @return bool
     */
    private function hasStrictNullComparison($sheetExport): bool
    {
        return $sheetExport instanceof WithStrictNullComparison;
    }

    /**
     * @param object|WithCustomChunkSize $export
     *
     * @return int
     */
    private function getChunkSize($export): int
    {
        if ($export instanceof WithCustomChunkSize) {
            return $export->chunkSize();
        }

        return $this->chunkSize;
    }
}
