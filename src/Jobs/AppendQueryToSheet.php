<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Writer;
use Maatwebsite\Excel\Files\TemporaryFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AppendQueryToSheet implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var SerializedQuery
     */
    public $query;

    /**
     * @var string
     */
    public $temporaryFile;

    /**
     * @var string
     */
    public $writerType;

    /**
     * @var int
     */
    public $sheetIndex;

    /**
     * @var object
     */
    public $sheetExport;

    /**
     * @param object          $sheetExport
     * @param TemporaryFile   $temporaryFile
     * @param string          $writerType
     * @param int             $sheetIndex
     * @param SerializedQuery $query
     */
    public function __construct(
        $sheetExport,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex,
        SerializedQuery $query
    ) {
        $this->sheetExport   = $sheetExport;
        $this->query         = $query;
        $this->temporaryFile = $temporaryFile;
        $this->writerType    = $writerType;
        $this->sheetIndex    = $sheetIndex;
    }

    /**
     * @param Writer $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(Writer $writer)
    {
        $writer = $writer->reopen($this->temporaryFile, $this->writerType);

        $sheet = $writer->getSheetByIndex($this->sheetIndex);

        $sheet->appendRows($this->query->execute(), $this->sheetExport);

        $writer->write($this->sheetExport, $this->temporaryFile, $this->writerType);
    }
}
