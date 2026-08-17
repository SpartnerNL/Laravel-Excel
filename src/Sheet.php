<?php

namespace Maatwebsite\Excel;

use Closure;
use Generator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Columns\ColumnCollection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\FromIterator;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromScout;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithFormatData;
use Maatwebsite\Excel\Concerns\WithFreezePane;
use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithPageBreaks;
use Maatwebsite\Excel\Concerns\WithPrintArea;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithSheetProtection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTabColor;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Exceptions\ConcernConflictException;
use Maatwebsite\Excel\Exceptions\RowSkippedException;
use Maatwebsite\Excel\Exceptions\SheetNotFoundException;
use Maatwebsite\Excel\Exceptions\UnsupportedColumnException;
use Maatwebsite\Excel\Files\TemporaryFileFactory;
use Maatwebsite\Excel\Helpers\ArrayHelper;
use Maatwebsite\Excel\Helpers\CellHelper;
use Maatwebsite\Excel\Imports\EndRowFinder;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use Maatwebsite\Excel\Imports\ModelImporter;
use Maatwebsite\Excel\Validators\RowValidator;
use Maatwebsite\Excel\Validators\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Cell as SpreadsheetCell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

/** @mixin Worksheet */
class Sheet
{
    use DelegatedMacroable, HasEventBus;

    protected int $chunkSize;

    protected TemporaryFileFactory $temporaryFileFactory;

    protected ?Export $exportable = null;

    protected ?ColumnCollection $columns = null;

    final public function __construct(
        private Worksheet $worksheet,
    ) {
        $this->chunkSize            = config('excel.exports.chunk_size', 100);
        $this->temporaryFileFactory = app(TemporaryFileFactory::class);
    }

    /**
     * @throws Exception
     * @throws SheetNotFoundException
     */
    public static function make(Spreadsheet $spreadsheet, string|int $index): Sheet
    {
        if (is_numeric($index)) {
            return self::byIndex($spreadsheet, $index);
        }

        return self::byName($spreadsheet, $index);
    }

    /**
     * @throws Exception
     * @throws SheetNotFoundException
     */
    public static function byIndex(Spreadsheet $spreadsheet, int $index): Sheet
    {
        if (!isset($spreadsheet->getAllSheets()[$index])) {
            throw SheetNotFoundException::byIndex($index, $spreadsheet->getSheetCount());
        }

        return new static($spreadsheet->getSheet($index));
    }

    /**
     * @throws SheetNotFoundException
     */
    public static function byName(Spreadsheet $spreadsheet, string $name): Sheet
    {
        if (!$spreadsheet->sheetNameExists($name)) {
            throw SheetNotFoundException::byName($name);
        }

        return new static($spreadsheet->getSheetByName($name));
    }

    /**
     * @throws Exception
     */
    public function open(Export $sheetExport): void
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
            $title = $sheetExport->title();

            $title = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $title);
            if (StringHelper::countCharacters($title) > Worksheet::SHEET_TITLE_MAXIMUM_LENGTH) {
                $title = StringHelper::substring($title, 0, Worksheet::SHEET_TITLE_MAXIMUM_LENGTH);
            }

            $this->worksheet->setTitle($title);
        }

        if (($sheetExport instanceof FromQuery
                || $sheetExport instanceof FromCollection
                || $sheetExport instanceof FromArray
                || $sheetExport instanceof FromScout)
            && $sheetExport instanceof FromView) {
            throw ConcernConflictException::queryOrCollectionAndView();
        }

        if ($sheetExport instanceof WithColumns) {
            if ($sheetExport instanceof WithHeadings) {
                throw ConcernConflictException::columnsAndHeadings();
            }

            if ($sheetExport instanceof WithMapping) {
                throw ConcernConflictException::columnsAndMapping();
            }
        }

        if ($sheetExport instanceof WithColumns) {
            $columns = $this->columnsFor($sheetExport);

            if ($columns->contains(fn (Column $column): bool => $column->hasMultiple())) {
                throw UnsupportedColumnException::multipleOnExport();
            }

            // Headings go through the columns themselves, so they use the same
            // addressing as the data and cannot drift out of alignment with it.
            $columns->writeHeadings($this->worksheet, $this->headingRowFor($sheetExport));
        } elseif (!$sheetExport instanceof FromView && $sheetExport instanceof WithHeadings) {
            if ($sheetExport instanceof WithCustomStartCell) {
                $startCell = $sheetExport->startCell();
            }

            $this->append(
                ArrayHelper::ensureMultipleRows($sheetExport->headings()),
                $startCell ?? null,
                $this->hasStrictNullComparison($sheetExport)
            );
        }
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function export(Export $sheetExport): void
    {
        $this->open($sheetExport);

        $handler = app(HandlerRegistry::class)->findSyncHandler($sheetExport);
        $handler?->handle($this, $sheetExport);

        $this->close($sheetExport);
    }

    /**
     * @throws ValidationException
     */
    public function import(Import $import, int $startRow = 1): void
    {
        if ($import instanceof WithColumns) {
            if ($import instanceof WithMappedCells) {
                throw ConcernConflictException::columnsAndMappedCells();
            }

            if ($import instanceof WithColumnLimit) {
                throw ConcernConflictException::columnsAndColumnLimit();
            }

            if ($import instanceof WithGroupedHeadingRow) {
                throw ConcernConflictException::columnsAndGroupedHeadingRow();
            }
        }

        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        $this->raise(new BeforeSheet($this, $import));

        if ($import instanceof WithProgressBar && !$import instanceof WithChunkReading) {
            $import->getConsoleOutput()->progressStart($this->worksheet->getHighestRow());
        }

        $calculatesFormulas = $import instanceof WithCalculatedFormulas;
        $formatData         = $import instanceof WithFormatData;

        if ($import instanceof WithMappedCells) {
            app(MappedReader::class)->map($import, $this->worksheet);
        } else {
            if ($import instanceof ToModel) {
                app(ModelImporter::class)->import($this->worksheet, $import, $startRow);
            }

            if ($import instanceof ToCollection) {
                $rows = $this->toCollection($import, $startRow, null, $calculatesFormulas, $formatData);

                if ($import instanceof WithValidation) {
                    $rows = $this->validated($import, $startRow, $rows);
                }

                $import->collection($rows);
            }

            if ($import instanceof ToArray) {
                $rows = $this->toArray($import, $startRow, null, $calculatesFormulas, $formatData);

                if ($import instanceof WithValidation) {
                    $rows = $this->validated($import, $startRow, $rows);
                }

                $import->array($rows);
            }
        }

        if ($import instanceof OnEachRow) {
            $headingRow          = HeadingRowExtractor::extract($this->worksheet, $import);
            $headerIsGrouped     = HeadingRowExtractor::extractGrouping($headingRow, $import);
            $endColumn           = $import instanceof WithColumnLimit ? $import->endColumn() : null;
            $preparationCallback = $this->getPreparationCallback($import);
            $columns             = $this->columnsForImport($import, $headingRow);

            foreach ($this->worksheet->getRowIterator()->resetStart($startRow) as $row) {
                $sheetRow = new Row($row, $headingRow, $headerIsGrouped, $columns);

                if ($import instanceof WithValidation) {
                    $sheetRow->setPreparationCallback($preparationCallback);
                }

                $rowArray                    = $sheetRow->toArray(null, $import instanceof WithCalculatedFormulas, $import instanceof WithFormatData, $endColumn);
                $rowIsEmptyAccordingToImport = $import instanceof SkipsEmptyRows && method_exists($import, 'isEmptyWhen') && $import->isEmptyWhen($rowArray);
                if (!$import instanceof SkipsEmptyRows || (!$rowIsEmptyAccordingToImport && !$sheetRow->isEmpty($calculatesFormulas))) {
                    if ($import instanceof WithValidation) {
                        $toValidate = [$sheetRow->getIndex() => $rowArray];

                        try {
                            app(RowValidator::class)->validate($toValidate, $import);
                            $import->onRow($sheetRow);
                        } catch (RowSkippedException) {
                        } catch (Throwable $e) {
                            if ($import instanceof SkipsOnError) {
                                $import->onError($e);
                            } else {
                                throw $e;
                            }
                        }
                    } else {
                        try {
                            $import->onRow($sheetRow);
                        } catch (Throwable $e) {
                            if ($import instanceof SkipsOnError) {
                                $import->onError($e);
                            } else {
                                throw $e;
                            }
                        }
                    }
                }

                if ($import instanceof WithProgressBar) {
                    $import->getConsoleOutput()->progressAdvance();
                }
            }
        }

        $this->raise(new AfterSheet($this, $import));

        if ($import instanceof WithProgressBar && !$import instanceof WithChunkReading) {
            $import->getConsoleOutput()->progressFinish();
        }
    }

    /**
     * @return array<int, array<array-key, mixed>>
     */
    public function toArray(?Import $import, ?int $startRow = null, mixed $nullValue = null, bool $calculateFormulas = false, bool $formatData = false): array
    {
        if ($startRow > $this->worksheet->getHighestRow()) {
            return [];
        }

        $endRow          = EndRowFinder::find($import, $startRow, $this->worksheet->getHighestRow());
        $headingRow      = HeadingRowExtractor::extract($this->worksheet, $import);
        $headerIsGrouped = HeadingRowExtractor::extractGrouping($headingRow, $import);
        $endColumn       = $import instanceof WithColumnLimit ? $import->endColumn() : null;
        $columns         = $this->columnsForImport($import, $headingRow);

        $rows = [];
        foreach ($this->worksheet->getRowIterator($startRow, $endRow) as $index => $row) {
            $row = new Row($row, $headingRow, $headerIsGrouped, $columns);

            if ($import instanceof SkipsEmptyRows && $row->isEmpty($calculateFormulas, $endColumn)) {
                continue;
            }

            $row = $row->toArray($nullValue, $calculateFormulas, $formatData, $endColumn);

            if ($import && method_exists($import, 'isEmptyWhen') && $import->isEmptyWhen($row)) {
                continue;
            }

            if ($import instanceof WithMapping) {
                $row = $import->map($row);
            }

            if ($import instanceof WithValidation && method_exists($import, 'prepareForValidation')) {
                $row = $import->prepareForValidation($row, $index);
            }

            $rows[] = $row;

            if ($import instanceof WithProgressBar) {
                $import->getConsoleOutput()->progressAdvance();
            }
        }

        return $rows;
    }

    /**
     * @return Collection<int, Collection<array-key, mixed>>
     */
    public function toCollection(?Import $import, ?int $startRow = null, mixed $nullValue = null, bool $calculateFormulas = false, bool $formatData = false): Collection
    {
        $rows = $this->toArray($import, $startRow, $nullValue, $calculateFormulas, $formatData);

        return new Collection(array_map(fn (array $row): Collection => new Collection($row), $rows));
    }

    /**
     * @throws Exception
     */
    public function close(Export $sheetExport): void
    {
        // Runs here rather than at the end of export() so the queued path gets it
        // too: CloseSheet is the only point where every chunk has been written and
        // getHighestRow() reflects the whole sheet.
        if ($sheetExport instanceof WithColumns) {
            $this->columnsFor($sheetExport)->afterWriting(
                $this->worksheet,
                $this->headingRowFor($sheetExport)
            );
        }

        if ($sheetExport instanceof WithCharts) {
            $this->addCharts($sheetExport->charts());
        }

        if ($sheetExport instanceof WithDrawings) {
            $this->addDrawings($sheetExport->drawings());
        }

        $this->exportable = $sheetExport;

        if ($sheetExport instanceof WithColumnFormatting) {
            foreach ($sheetExport->columnFormats() as $column => $format) {
                $this->formatColumn($column, $format);
            }
        }

        if ($sheetExport instanceof ShouldAutoSize) {
            $this->autoSize();
        }

        if ($sheetExport instanceof WithFreezePane) {
            $this->worksheet->freezePane($sheetExport->freezePane());
        }

        if ($sheetExport instanceof WithTabColor) {
            $this->worksheet->getTabColor()->setRGB($sheetExport->tabColor());
        }

        if ($sheetExport instanceof WithSheetProtection) {
            $protection = $this->worksheet->getProtection();
            $protection->setSheet(true);
            $password = $sheetExport->sheetProtection();
            if ($password !== null) {
                $protection->setPassword($password);
            }
        }

        if ($sheetExport instanceof WithPageBreaks) {
            foreach ($sheetExport->pageBreaks() as $row) {
                $this->worksheet->setBreak('A' . $row, Worksheet::BREAK_ROW);
            }
        }

        if ($sheetExport instanceof WithPrintArea) {
            $this->worksheet->getPageSetup()->setPrintArea($sheetExport->printArea());
        }

        if ($sheetExport instanceof WithColumnWidths) {
            foreach ($sheetExport->columnWidths() as $column => $width) {
                $this->worksheet->getColumnDimension($column)->setAutoSize(false)->setWidth($width);
            }
        }

        if ($sheetExport instanceof WithStyles) {
            $styles = $sheetExport->styles($this->worksheet);
            if (is_array($styles)) {
                foreach ($styles as $coordinate => $coordinateStyles) {
                    if (is_numeric($coordinate)) {
                        $coordinate = 'A' . $coordinate . ':' . $this->worksheet->getHighestColumn($coordinate) . $coordinate;
                    }

                    $this->worksheet->getStyle($coordinate)->applyFromArray($coordinateStyles);
                }
            }
        }

        $this->raise(new AfterSheet($this, $this->exportable));

        $this->clearListeners();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function fromView(FromView $sheetExport, ?int $sheetIndex = null): void
    {
        $temporaryFile = $this->temporaryFileFactory->makeLocal(null, 'html');
        $temporaryFile->put($sheetExport->view()->render());

        $spreadsheet = $this->worksheet->getParent();

        /** @var Html $reader */
        $reader = IOFactory::createReader('Html');

        // If no sheetIndex given, insert content into the last sheet
        $reader->setSheetIndex($sheetIndex ?? $spreadsheet->getSheetCount() - 1);
        $reader->loadIntoExisting($temporaryFile->getLocalPath(), $spreadsheet);

        $temporaryFile->delete();
    }

    public function fromQuery(FromQuery $sheetExport, Worksheet $worksheet): void
    {
        $query = $sheetExport->query();

        // Operate on a clone to avoid altering the original
        // and use the clone operator directly to support old versions of Laravel
        // that don't have a clone method in eloquent
        $clonedQuery = clone $query;
        $clonedQuery->chunk($this->getChunkSize($sheetExport), function (iterable $chunk) use ($sheetExport): void {
            $this->appendRows($chunk, $sheetExport);
        });
    }

    public function fromScout(FromScout $sheetExport, Worksheet $worksheet): void
    {
        $scout     = $sheetExport->scout();
        $chunkSize = $this->getChunkSize($sheetExport);

        $chunk = $scout->paginate($chunkSize);
        // Append first page
        $this->appendRows($chunk->items(), $sheetExport);

        // Append rest of pages
        for ($page = 2; $page <= $chunk->lastPage(); $page++) {
            $this->appendRows($scout->paginate($chunkSize, 'page', $page)->items(), $sheetExport);
        }
    }

    public function fromCollection(FromCollection $sheetExport): void
    {
        $this->appendRows($sheetExport->collection()->all(), $sheetExport);
    }

    public function fromArray(FromArray $sheetExport): void
    {
        $this->appendRows($sheetExport->array(), $sheetExport);
    }

    public function fromIterator(FromIterator $sheetExport): void
    {
        $iterator = class_exists(LazyCollection::class) ? new LazyCollection(function () use ($sheetExport) {
            foreach ($sheetExport->iterator() as $row) {
                yield $row;
            }
        }) : $sheetExport->iterator();

        $this->appendRows($iterator, $sheetExport);
    }

    public function fromGenerator(FromGenerator $sheetExport): void
    {
        $generator = class_exists(LazyCollection::class) ? new LazyCollection(function () use ($sheetExport) {
            foreach ($sheetExport->generator() as $row) {
                yield $row;
            }
        }) : $sheetExport->generator();

        $this->appendRows($generator, $sheetExport);
    }

    /**
     * @param  array<array-key, mixed>  $rows
     */
    public function append(array $rows, ?string $startCell = null, bool $strictNullComparison = false): void
    {
        if (!$startCell) {
            $startCell = 'A1';
        }

        if ($this->hasRows($startCell)) {
            $startCell = CellHelper::getColumnFromCoordinate($startCell) . ($this->worksheet->getHighestRow() + 1);
        }

        $this->worksheet->fromArray($rows, null, $startCell, $strictNullComparison);
    }

    public function autoSize(): void
    {
        foreach ($this->buildColumnRange('A', $this->worksheet->getHighestDataColumn()) as $col) {
            $dimension = $this->worksheet->getColumnDimension($col);

            // Only auto-size columns that have not had an explicit width.
            if ($dimension->getWidth() == -1) {
                $dimension->setAutoSize(true);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function formatColumn(string $column, string $format): void
    {
        // If the column is a range, we wouldn't need to calculate the range.
        if (stripos($column, ':') !== false) {
            $this->worksheet
                ->getStyle($column)
                ->getNumberFormat()
                ->setFormatCode($format);
        } else {
            $this->worksheet
                ->getStyle($column . '1:' . $column . $this->worksheet->getHighestRow())
                ->getNumberFormat()
                ->setFormatCode($format);
        }
    }

    public function chunkSize(int $chunkSize): Sheet
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    public function getDelegate(): Worksheet
    {
        return $this->worksheet;
    }

    /**
     * @param  Chart|Chart[]  $charts
     */
    public function addCharts(Chart|array $charts): void
    {
        $charts = \is_array($charts) ? $charts : [$charts];

        foreach ($charts as $chart) {
            $this->worksheet->addChart($chart);
        }
    }

    /**
     * @param  BaseDrawing|BaseDrawing[]  $drawings
     */
    public function addDrawings(BaseDrawing|array $drawings): void
    {
        $drawings = \is_array($drawings) ? $drawings : [$drawings];

        foreach ($drawings as $drawing) {
            $drawing->setWorksheet($this->worksheet);
        }
    }

    public function hasConcern(string $concern): bool
    {
        return $this->exportable instanceof $concern;
    }

    /**
     * @param  iterable<array-key, mixed>  $rows
     */
    public function appendRows(iterable $rows, Export $sheetExport): void
    {
        // The append jobs build their own Sheet without going through open(), so
        // hasConcern() would otherwise report false for the whole queued path.
        $this->exportable ??= $sheetExport;

        if (method_exists($sheetExport, 'prepareRows')) {
            $rows = $sheetExport->prepareRows($rows);
        }

        if ($sheetExport instanceof WithColumns) {
            $this->appendRowsWithColumns($rows, $this->columnsFor($sheetExport), $sheetExport);

            return;
        }

        $rows = $rows instanceof LazyCollection ? $rows : new Collection($rows);

        $rows->flatMap(function ($row) use ($sheetExport): array {
            if ($sheetExport instanceof WithMapping) {
                $row = $sheetExport->map($row);
            }

            if ($sheetExport instanceof WithCustomValueBinder) {
                SpreadsheetCell::setValueBinder($sheetExport);
            }

            return ArrayHelper::ensureMultipleRows(
                static::mapArraybleRow($row)
            );
        })->chunk(1000)->each(function ($rows) use ($sheetExport): void {
            $this->append(
                $rows->toArray(),
                $sheetExport instanceof WithCustomStartCell ? $sheetExport->startCell() : null,
                $this->hasStrictNullComparison($sheetExport)
            );
        });
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function mapArraybleRow(mixed $row): array
    {
        // When dealing with eloquent models, we'll skip the relations
        // as we won't be able to display them anyway.
        if (is_object($row) && method_exists($row, 'attributesToArray')) {
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

    public function getStartRow(?Import $sheetImport): int
    {
        return HeadingRowExtractor::determineStartRow($sheetImport);
    }

    /**
     * Disconnect the sheet.
     */
    public function disconnect(): void
    {
        $this->worksheet->disconnectCells();
        unset($this->worksheet);
    }

    /**
     * @param  iterable<array-key, mixed>  $rows
     *
     * @throws Exception
     */
    protected function appendRowsWithColumns(iterable $rows, ColumnCollection $columns, Export $sheetExport): void
    {
        $rowNumber = max($this->worksheet->getHighestRow(), $this->headingRowFor($sheetExport));

        foreach ($rows as $row) {
            $rowNumber++;

            // The binder is a static that other jobs may have reset, and untyped
            // columns fall through to setValue(), which consults it.
            if ($sheetExport instanceof WithCustomValueBinder) {
                SpreadsheetCell::setValueBinder($sheetExport);
            }

            $columns->each(function (Column $column) use ($rowNumber, $row): void {
                $column->write($this->worksheet, $rowNumber, $row);
            });
        }
    }

    /**
     * @param  Collection<int, Collection<array-key, mixed>>|array<int, array<array-key, mixed>>  $rows
     * @return Collection<int, Collection<array-key, mixed>>|array<int, array<array-key, mixed>>
     */
    protected function validated(WithValidation $import, int $startRow, Collection|array $rows): Collection|array
    {
        $toValidate = (new Collection($rows))->mapWithKeys(fn ($row, $index): array => [($startRow + $index) => $row]);

        try {
            app(RowValidator::class)->validate($toValidate->toArray(), $import);
        } catch (RowSkippedException $e) {
            foreach ($e->skippedRows() as $row) {
                unset($rows[$row - $startRow]);
            }
        }

        return $rows;
    }

    protected function buildColumnRange(string $lower, string $upper): Generator
    {
        /**
         * @callable(string): string $increment
         */
        $increment = function_exists('str_increment') ? str_increment(...) : (fn ($cell): int|float => ++$cell);

        $upper = $increment($upper);
        for ($i = $lower; $i !== $upper; $i = $increment($i)) {
            yield $i;
        }
    }

    /**
     * The columns are always derived from the export currently being written, so a
     * queued job resolves the same set without depending on open() having run in
     * the same process.
     *
     * @throws Exception
     */
    protected function columnsFor(Export $sheetExport): ColumnCollection
    {
        return $this->columns ??= ColumnCollection::makeFrom(
            $sheetExport,
            null,
            $this->startColumnFor($sheetExport) - 1
        );
    }

    /**
     * The row the headings occupy; data starts on the row after it.
     */
    private function headingRowFor(Export $sheetExport): int
    {
        if (!$sheetExport instanceof WithCustomStartCell) {
            return 1;
        }

        return (int) Coordinate::coordinateFromString($sheetExport->startCell())[1];
    }

    /**
     * Positionally declared columns are offset by the start cell; columns given an
     * explicit letter are absolute.
     */
    private function startColumnFor(Export $sheetExport): int
    {
        if (!$sheetExport instanceof WithCustomStartCell) {
            return 1;
        }

        return Coordinate::columnIndexFromString(
            Coordinate::coordinateFromString($sheetExport->startCell())[0]
        );
    }

    /**
     * @param  list<string>  $headingRow
     */
    private function columnsForImport(?Import $import, array $headingRow): ?ColumnCollection
    {
        return $import instanceof WithColumns
            ? ColumnCollection::makeFrom($import, $headingRow)
            : null;
    }

    private function hasRows(string $startCell): bool
    {
        return $this->worksheet->cellExists($startCell);
    }

    private function hasStrictNullComparison(Export $sheetExport): bool
    {
        if ($sheetExport instanceof WithStrictNullComparison) {
            return true;
        }

        return config('excel.exports.strict_null_comparison', false);
    }

    private function getChunkSize(Export $export): int
    {
        if ($export instanceof WithCustomChunkSize) {
            return $export->chunkSize();
        }

        return $this->chunkSize;
    }

    private function getPreparationCallback(Import $import): ?Closure
    {
        if (!$import instanceof WithValidation || !method_exists($import, 'prepareForValidation')) {
            return null;
        }

        return fn (array $data, int $index) => $import->prepareForValidation($data, $index);
    }
}
