<?php

namespace Maatwebsite\Excel;

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithFormatData;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Maatwebsite\Excel\Exceptions\SheetNotFoundException;
use Maatwebsite\Excel\Factories\ReaderFactory;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFileFactory;
use Maatwebsite\Excel\Transactions\TransactionHandler;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Reader\IReader2;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

/** @mixin Spreadsheet */
class Reader
{
    use DelegatedMacroable, HasEventBus;

    protected ?Spreadsheet $spreadsheet = null;

    /**
     * @var ?object[]
     */
    protected ?array $sheetImports = [];

    protected TemporaryFile $currentFile;

    protected TransactionHandler $transaction;

    protected IReader $reader;

    public function __construct(
        protected TemporaryFileFactory $temporaryFileFactory,
        TransactionHandler $transaction,
    ) {
        $this->setDefaultValueBinder();

        $this->transaction = $transaction;
    }

    /**
     * @return list<string>
     */
    public function __sleep(): array
    {
        return ['spreadsheet', 'sheetImports', 'currentFile', 'temporaryFileFactory', 'reader'];
    }

    public function __wakeup(): void
    {
        $this->transaction = app(TransactionHandler::class);
    }

    /**
     * @return static|PendingDispatch|PendingBatch|Collection<int, object>|null
     *
     * @throws FileNotFoundException
     * @throws NoTypeDetectedException
     * @throws SheetNotFoundException
     * @throws Throwable
     */
    public function read(object $import, string|UploadedFile $filePath, ?string $readerType = null, ?string $disk = null): static|PendingDispatch|PendingBatch|Collection|null
    {
        $this->reader = $this->getReader($import, $filePath, $readerType, $disk);

        if ($import instanceof WithChunkReading) {
            return app(ChunkReader::class)->read($import, $this, $this->currentFile);
        }

        try {
            $this->loadSpreadsheet($import);

            ($this->transaction)(function () use ($import): void {
                $sheetsToDisconnect = [];

                foreach ($this->sheetImports as $index => $sheetImport) {
                    if (($sheet = $this->getSheet($import, $sheetImport, $index)) instanceof Sheet) {
                        $sheet->import($sheetImport, $sheet->getStartRow($sheetImport));

                        // when using WithCalculatedFormulas we need to keep the sheet until all sheets are imported
                        if (!($sheetImport instanceof HasReferencesToOtherSheets)) {
                            $sheet->disconnect();
                        } else {
                            $sheetsToDisconnect[] = $sheet;
                        }
                    }
                }

                foreach ($sheetsToDisconnect as $sheet) {
                    $sheet->disconnect();
                }
            });

            $this->afterImport($import);
        } catch (Throwable $e) {
            $this->raise(new ImportFailed($e));
            $this->garbageCollect();
            throw $e;
        }

        return $this;
    }

    /**
     * @return array<array-key, array<int, array<array-key, mixed>>>
     *
     * @throws FileNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws NoTypeDetectedException
     * @throws SheetNotFoundException
     */
    public function toArray(object $import, string|UploadedFile $filePath, ?string $readerType = null, ?string $disk = null): array
    {
        $this->reader = $this->getReader($import, $filePath, $readerType, $disk);

        $this->loadSpreadsheet($import);

        $sheets             = [];
        $sheetsToDisconnect = [];
        foreach ($this->sheetImports as $index => $sheetImport) {
            $calculatesFormulas = $sheetImport instanceof WithCalculatedFormulas;
            $formatData         = $sheetImport instanceof WithFormatData;
            if (($sheet = $this->getSheet($import, $sheetImport, $index)) instanceof Sheet) {
                $sheets[$index] = $sheet->toArray($sheetImport, $sheet->getStartRow($sheetImport), null, $calculatesFormulas, $formatData);

                // when using WithCalculatedFormulas we need to keep the sheet until all sheets are imported
                if (!($sheetImport instanceof HasReferencesToOtherSheets)) {
                    $sheet->disconnect();
                } else {
                    $sheetsToDisconnect[] = $sheet;
                }
            }
        }

        foreach ($sheetsToDisconnect as $sheet) {
            $sheet->disconnect();
        }

        $this->afterImport($import);

        return $sheets;
    }

    /**
     * @return Collection<array-key, Collection<int, Collection<array-key, mixed>>>
     *
     * @throws FileNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws NoTypeDetectedException
     * @throws SheetNotFoundException
     */
    public function toCollection(?object $import, string|UploadedFile $filePath, ?string $readerType = null, ?string $disk = null): Collection
    {
        $this->reader = $this->getReader($import, $filePath, $readerType, $disk);
        $this->loadSpreadsheet($import);

        $sheets             = new Collection;
        $sheetsToDisconnect = [];
        foreach ($this->sheetImports as $index => $sheetImport) {
            $calculatesFormulas = $sheetImport instanceof WithCalculatedFormulas;
            $formatData         = $sheetImport instanceof WithFormatData;
            if (($sheet = $this->getSheet($import, $sheetImport, $index)) instanceof Sheet) {
                $sheets->put($index, $sheet->toCollection($sheetImport, $sheet->getStartRow($sheetImport), null, $calculatesFormulas, $formatData));

                // when using WithCalculatedFormulas we need to keep the sheet until all sheets are imported
                if (!($sheetImport instanceof HasReferencesToOtherSheets)) {
                    $sheet->disconnect();
                } else {
                    $sheetsToDisconnect[] = $sheet;
                }
            }
        }

        foreach ($sheetsToDisconnect as $sheet) {
            $sheet->disconnect();
        }

        $this->afterImport($import);

        return $sheets;
    }

    public function getDelegate(): Spreadsheet
    {
        return $this->spreadsheet;
    }

    public function setDefaultValueBinder(): self
    {
        Cell::setValueBinder(
            app(config('excel.value_binder.default', DefaultValueBinder::class))
        );

        return $this;
    }

    public function loadSpreadsheet(?object $import): void
    {
        $this->sheetImports = $this->buildSheetImports($import);

        $this->readSpreadsheet();

        // When no multiple sheets, use the main import object
        // for each loaded sheet in the spreadsheet
        if (!$import instanceof WithMultipleSheets) {
            $this->sheetImports = array_fill(0, $this->spreadsheet->getSheetCount(), $import);
        }

        $this->beforeImport($import);
    }

    public function readSpreadsheet(): void
    {
        $this->spreadsheet = $this->reader->load(
            $this->currentFile->getLocalPath()
        );
    }

    public function beforeImport(?object $import): void
    {
        $this->raise(new BeforeImport($this, $import));
    }

    public function afterImport(?object $import): void
    {
        $this->raise(new AfterImport($this, $import));

        $this->garbageCollect();
    }

    public function getPhpSpreadsheetReader(): IReader
    {
        return $this->reader;
    }

    /**
     * @return array<array-key, object>
     */
    public function getWorksheets(object $import): array
    {
        // Csv doesn't have worksheets.
        if (!$this->reader instanceof IReader2) {
            return ['Worksheet' => $import];
        }

        $worksheets = [];

        /** @var list<string> $worksheetNames */
        $worksheetNames = $this->reader->listWorksheetNames($this->currentFile->getLocalPath());
        if ($import instanceof WithMultipleSheets) {
            $sheetImports = $import->sheets();

            foreach ($sheetImports as $index => $sheetImport) {
                // Translate index to name.
                if (is_numeric($index)) {
                    $index = $worksheetNames[$index] ?? $index;
                }

                // Specify with worksheet name should have which import.
                $worksheets[$index] = $sheetImport;
            }

            // Load specific sheets.
            $this->reader->setLoadSheetsOnly(
                collect($worksheetNames)->intersect(array_keys($worksheets))->values()->all()
            );
        } else {
            // Each worksheet the same import class.
            foreach ($worksheetNames as $name) {
                $worksheets[$name] = $import;
            }
        }

        return $worksheets;
    }

    /**
     * @return array<string, int>
     */
    public function getTotalRows(): array
    {
        assert(method_exists($this->reader, 'listWorksheetInfo'));
        $info = $this->reader->listWorksheetInfo($this->currentFile->getLocalPath());

        $totalRows = [];
        foreach ($info as $sheet) {
            $totalRows[$sheet['worksheetName']] = $sheet['totalRows'];
        }

        return $totalRows;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws SheetNotFoundException
     */
    protected function getSheet(?object $import, ?object $sheetImport, string|int $index): ?Sheet
    {
        try {
            return Sheet::make($this->spreadsheet, $index);
        } catch (SheetNotFoundException $e) {
            if ($import instanceof SkipsUnknownSheets) {
                $import->onUnknownSheet($index);

                return null;
            }

            if ($sheetImport instanceof SkipsUnknownSheets) {
                $sheetImport->onUnknownSheet($index);

                return null;
            }

            throw $e;
        }
    }

    /**
     * @return array<array-key, object>
     */
    private function buildSheetImports(?object $import): array
    {
        $sheetImports = [];
        if ($import instanceof WithMultipleSheets) {
            $sheetImports = $import->sheets();

            // When only sheet names are given and the reader has
            // an option to load only the selected sheets.
            if (count(array_filter(array_keys($sheetImports), is_numeric(...))) === 0) {
                $this->reader->setLoadSheetsOnly(array_keys($sheetImports));
            }

            $this->reader->setCreateBlankSheetIfNoneRead(true);
        }

        return $sheetImports;
    }

    /**
     * @throws FileNotFoundException
     * @throws NoTypeDetectedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function getReader(?object $import, string|UploadedFile $filePath, ?string $readerType = null, ?string $disk = null): IReader
    {
        $shouldQueue = $import instanceof ShouldQueue;
        if ($shouldQueue && !$import instanceof WithChunkReading) {
            throw new InvalidArgumentException('ShouldQueue is only supported in combination with WithChunkReading.');
        }

        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        if ($import instanceof WithCustomValueBinder) {
            Cell::setValueBinder($import);
        }

        $fileExtension     = pathinfo($filePath, PATHINFO_EXTENSION);
        $temporaryFile     = $shouldQueue ? $this->temporaryFileFactory->make($fileExtension) : $this->temporaryFileFactory->makeLocal(null, $fileExtension);
        $this->currentFile = $temporaryFile->copyFrom(
            $filePath,
            $disk
        );

        return ReaderFactory::make(
            $import,
            $this->currentFile,
            $readerType
        );
    }

    /**
     * Garbage collect.
     */
    private function garbageCollect(): void
    {
        $this->clearListeners();
        $this->setDefaultValueBinder();

        // Force garbage collecting
        $this->sheetImports = [];
        $this->spreadsheet  = null;

        $this->currentFile->delete();
    }
}
