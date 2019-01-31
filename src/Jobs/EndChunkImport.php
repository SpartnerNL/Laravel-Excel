<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\HasEventBus;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Contracts\Queue\ShouldQueue;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Maatwebsite\Excel\Events\AfterChunkImport;

class EndChunkImport implements ShouldQueue
{
    use HasEventBus, Queueable;

    /**
     * @var IReader
     */
    private $reader;

    /**
     * @var object
     */
    private $import;

    /**
     * @param IReader $reader
     * @param object      $import
     */
    public function __construct(IReader $reader, $import)
    {
        $this->reader = $reader;
        $this->import = $import;
    }

    public function handle()
    {
        if ($this->import instanceof WithEvents) {
            $this->registerListeners($this->import->registerEvents());
        }

        $this->raise(new AfterChunkImport($this->reader, $this->import));
    }
}
