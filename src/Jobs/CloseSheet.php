<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Writer;

class CloseSheet implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, ProxyFailures, Queueable;

    /**
     * @param  object  $sheetExport
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     * @param  int  $sheetIndex
     */
    public function __construct(private $sheetExport, private TemporaryFile $temporaryFile, private string $writerType, private int $sheetIndex)
    {
    }

    /**
     * @param  Writer  $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(Writer $writer)
    {
        // Determine if the batch has been cancelled...
        if ($this->batch()?->cancelled()) {
            return;
        }

        $writer = $writer->reopen(
            $this->temporaryFile,
            $this->writerType
        );

        $sheet = $writer->getSheetByIndex($this->sheetIndex);

        if ($this->sheetExport instanceof WithEvents) {
            $sheet->registerListeners($this->sheetExport->registerEvents());
        }

        $sheet->close($this->sheetExport);

        $writer->write(
            $this->sheetExport,
            $this->temporaryFile,
            $this->writerType
        );
    }
}
