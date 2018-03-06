<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Writer;

class QueuedExportEvents
{
    use Queueable;

    /**
     * @var string
     */
    private $tempPath;

    /**
     * @var WithEvents
     */
    private $export;

    /**
     * @var string
     */
    private $writerType;

    /**
     * @param WithEvents $export
     * @param string     $tempPath
     * @param string     $writerType
     */
    public function __construct(WithEvents $export, string $tempPath, string $writerType)
    {
        $this->tempPath   = $tempPath;
        $this->export     = $export;
        $this->writerType = $writerType;
    }

    /**
     * @param Writer $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(Writer $writer)
    {
        $writer->reopen($this->tempPath, $this->writerType);

        $writer->registerListeners($this->export->registerEvents());

        $writer->raise(new BeforeWriting($writer));
    }
}