<?php

namespace Maatwebsite\Excel\Jobs;

use Maatwebsite\Excel\Writer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QueueExport implements ShouldQueue
{
    use ExtendedQueueable, Dispatchable;

    /**
     * @var object
     */
    private $export;

    /**
     * @var string
     */
    private $tempFile;

    /**
     * @var string
     */
    private $writerType;

    /**
     * @param object $export
     * @param string $tempFile
     * @param string $writerType
     */
    public function __construct($export, string $tempFile, string $writerType)
    {
        $this->export     = $export;
        $this->tempFile   = $tempFile;
        $this->writerType = $writerType;
    }

    /**
     * @param Writer $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function handle(Writer $writer)
    {
        $writer->open($this->export);

        $sheetExports = [$this->export];
        if ($this->export instanceof WithMultipleSheets) {
            $sheetExports = $this->export->sheets();
        }

        // Pre-create the worksheets
        foreach ($sheetExports as $sheetIndex => $sheetExport) {
            $sheet = $writer->addNewSheet($sheetIndex);
            $sheet->open($sheetExport);
        }

        // Write to temp file with empty sheets.
        $writer->write($this->tempFile, $this->writerType);
    }
}
