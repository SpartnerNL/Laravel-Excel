<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Writer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AppendDataToSheet implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var string
     */
    public $fileName;

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
     * @param object $sheetExport
     * @param string $fileName
     * @param string $writerType
     * @param int    $sheetIndex
     * @param array  $data
     */
    public function __construct($sheetExport, string $fileName, string $writerType, int $sheetIndex, array $data)
    {
        $this->sheetExport = $sheetExport;
        $this->data        = $data;
        $this->fileName    = $fileName;
        $this->writerType  = $writerType;
        $this->sheetIndex  = $sheetIndex;
    }

    /**
     * @param Writer $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(Writer $writer)
    {
        $writer = $writer->reopen($this->fileName, $this->writerType);

        $sheet = $writer->getSheetByIndex($this->sheetIndex);

        $sheet->appendRows($this->data, $this->sheetExport);

        $writer->write($this->sheetExport, $this->fileName, $this->writerType);
    }
}
