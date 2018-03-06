<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Writer;

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
    public $filePath;

    /**
     * @var string
     */
    public $writerType;

    /**
     * @var int
     */
    public $sheetIndex;

    /**
     * @param string $filePath
     * @param string $writerType
     * @param int    $sheetIndex
     * @param array  $data
     */
    public function __construct(string $filePath, string $writerType, int $sheetIndex, array $data)
    {
        $this->data       = $data;
        $this->filePath   = $filePath;
        $this->writerType = $writerType;
        $this->sheetIndex = $sheetIndex;
    }

    /**
     * @param Writer $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function handle(Writer $writer)
    {
        $writer = $writer->reopen($this->filePath, $this->writerType);

        $sheet = $writer->getSheetByIndex($this->sheetIndex);

        $sheet->append($this->data);

        $writer->write($this->filePath, $this->writerType);
    }
}