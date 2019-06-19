<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Writer;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Files\TemporaryFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AppendViewToSheet implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var TemporaryFile
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
     * @var FromView
     */
    public $sheetExport;

    /**
     * @param FromView        $sheetExport
     * @param TemporaryFile $temporaryFile
     * @param string        $writerType
     * @param int           $sheetIndex
     * @param array         $data
     */
    public function __construct(FromView $sheetExport, TemporaryFile $temporaryFile, string $writerType, int $sheetIndex)
    {
        $this->sheetExport   = $sheetExport;
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

        $sheet->fromView($this->sheetExport, $this->sheetIndex);

        $writer->write($this->sheetExport, $this->temporaryFile, $this->writerType);
    }
}
