<?php

namespace Maatwebsite\Excel\Jobs;

use Maatwebsite\Excel\Sheet;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Illuminate\Contracts\Queue\ShouldQueue;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Maatwebsite\Excel\Filters\ChunkReadFilter;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class ReadChunk implements ShouldQueue
{
    use Queueable;

    /**
     * @var IReader
     */
    private $reader;

    /**
     * @var string
     */
    private $file;

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
     * @param IReader $reader
     * @param string  $file
     * @param string  $sheetName
     * @param object  $sheetImport
     * @param int     $startRow
     * @param int     $chunkSize
     */
    public function __construct(IReader $reader, string $file, string $sheetName, $sheetImport, int $startRow, int $chunkSize)
    {
        $this->reader      = $reader;
        $this->file        = $file;
        $this->sheetName   = $sheetName;
        $this->sheetImport = $sheetImport;
        $this->startRow    = $startRow;
        $this->chunkSize   = $chunkSize;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle()
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

        $spreadsheet = $this->reader->load($this->file);

        $sheet = Sheet::byName(
            $spreadsheet,
            $this->sheetName
        );

        DB::transaction(function () use ($sheet) {
            $sheet->import(
                $this->sheetImport,
                $this->startRow
            );

            $sheet->disconnect();
        });
    }
}
