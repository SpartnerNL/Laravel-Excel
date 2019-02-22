<?php

namespace Maatwebsite\Excel\Jobs;

use Maatwebsite\Excel\Sheet;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Illuminate\Contracts\Queue\ShouldQueue;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Maatwebsite\Excel\Helpers\FilePathHelper;
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
    private $fileName;

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
     * @param string  $fileName
     * @param string  $sheetName
     * @param object  $sheetImport
     * @param int     $startRow
     * @param int     $chunkSize
     */
    public function __construct(IReader $reader, string $fileName, string $sheetName, $sheetImport, int $startRow, int $chunkSize)
    {
        $this->reader      = $reader;
        $this->fileName    = $fileName;
        $this->sheetName   = $sheetName;
        $this->sheetImport = $sheetImport;
        $this->startRow    = $startRow;
        $this->chunkSize   = $chunkSize;
    }

    /**
     * @param FilePathHelper $filePathHelper
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Maatwebsite\Excel\Exceptions\SheetNotFoundException
     * @throws \Maatwebsite\Excel\Exceptions\UnreadableFileException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(FilePathHelper $filePathHelper)
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

        $file        = $filePathHelper->getTempFile($this->fileName);
        $spreadsheet = $this->reader->load($file);

        $sheet = Sheet::byName(
            $spreadsheet,
            $this->sheetName
        );

        if ($sheet->getHighestRow() < $this->startRow) {
            $sheet->disconnect();

            return;
        }

        DB::transaction(function () use ($sheet) {
            $sheet->import(
                $this->sheetImport,
                $this->startRow
            );

            $sheet->disconnect();
        });
    }
}
