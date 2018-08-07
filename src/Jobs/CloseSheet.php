<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Writer;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Contracts\Queue\ShouldQueue;

class CloseSheet implements ShouldQueue
{
    use Queueable;

    /**
     * @var object
     */
    private $sheetExport;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $writerType;

    /**
     * @var int
     */
    private $sheetIndex;

    /**
     * @param object $sheetExport
     * @param string $filePath
     * @param string $writerType
     * @param int    $sheetIndex
     */
    public function __construct($sheetExport, string $filePath, string $writerType, int $sheetIndex)
    {
        $this->sheetExport = $sheetExport;
        $this->filePath    = $filePath;
        $this->writerType  = $writerType;
        $this->sheetIndex  = $sheetIndex;
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

        if ($this->sheetExport instanceof WithEvents) {
            $sheet->registerListeners($this->sheetExport->registerEvents());
        }

        $sheet->close($this->sheetExport);

        $writer->write($this->sheetExport, $this->filePath, $this->writerType);
    }
}
