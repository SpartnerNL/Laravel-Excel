<?php

namespace Maatwebsite\Excel\Jobs;

use Throwable;
use Maatwebsite\Excel\Sheet;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\HasEventBus;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Files\TemporaryFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Maatwebsite\Excel\Filters\ChunkReadFilter;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Transactions\TransactionHandler;

class ReadChunk implements ShouldQueue
{
    use Queueable, HasEventBus;

    /**
     * @var WithChunkReading
     */
    private $import;

    /**
     * @var IReader
     */
    private $reader;

    /**
     * @var TemporaryFile
     */
    private $temporaryFile;

    /**
     * @var string
     */
    private $sheetName;

    /**
     * @var object
     */
    private $sheetImport;

    /**
     * @var int
     */
    private $startRow;

    /**
     * @var int
     */
    private $chunkSize;

    /**
     * @param  WithChunkReading  $import
     * @param  IReader  $reader
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $sheetName
     * @param  object  $sheetImport
     * @param  int  $startRow
     * @param  int  $chunkSize
     */
    public function __construct(WithChunkReading $import, IReader $reader, TemporaryFile $temporaryFile, string $sheetName, $sheetImport, int $startRow, int $chunkSize)
    {
        $this->import        = $import;
        $this->reader        = $reader;
        $this->temporaryFile = $temporaryFile;
        $this->sheetName     = $sheetName;
        $this->sheetImport   = $sheetImport;
        $this->startRow      = $startRow;
        $this->chunkSize     = $chunkSize;
    }

    /**
     * @param  TransactionHandler  $transaction
     *
     * @throws \Maatwebsite\Excel\Exceptions\SheetNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(TransactionHandler $transaction)
    {
        if ($this->sheetImport instanceof WithCustomValueBinder) {
            Cell::setValueBinder($this->sheetImport);
        }

        $headingRow = HeadingRowExtractor::headingRow($this->sheetImport);

        $filter = new ChunkReadFilter(
            $headingRow,
            $this->startRow,
            $this->chunkSize,
            $this->sheetName
        );

        $this->reader->setReadFilter($filter);
        $this->reader->setReadDataOnly(true);
        $this->reader->setReadEmptyCells(false);

        $spreadsheet = $this->reader->load(
            $this->temporaryFile->sync()->getLocalPath()
        );

        $sheet = Sheet::byName(
            $spreadsheet,
            $this->sheetName
        );

        if ($sheet->getHighestRow() < $this->startRow) {
            $sheet->disconnect();

            return;
        }

        $transaction(function () use ($sheet) {
            $sheet->import(
                $this->sheetImport,
                $this->startRow
            );

            $sheet->disconnect();
        });
    }

    /**
     * @param  Throwable  $e
     */
    public function failed(Throwable $e)
    {
        if ($this->import instanceof WithEvents) {
            $this->registerListeners($this->import->registerEvents());
            $this->raise(new ImportFailed($e));
        }
    }
}
