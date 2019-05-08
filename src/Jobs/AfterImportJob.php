<?php

namespace Maatwebsite\Excel\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\HasEventBus;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;

class AfterImportJob implements ShouldQueue
{
    use Queueable, HasEventBus;

    /**
     * @var WithEvents
     */
    private $import;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param  object  $import
     * @param  Reader  $reader
     */
    public function __construct($import, Reader $reader)
    {
        $this->import = $import;
        $this->reader = $reader;
    }

    public function handle()
    {
        if ($this->import instanceof WithEvents) {
            if (null === $this->reader->getDelegate()) {
                $this->reader->readSpreadsheet();
            }

            $this->reader->registerListeners($this->import->registerEvents());
        }

        $this->reader->afterImport($this->import);
    }

    /**
     * @param  Throwable  $e
     */
    public function failed(Throwable $e)
    {
        if ($this->import instanceof WithEvents) {
            $this->registerListeners($this->import->registerEvents());
            $this->raise(new ImportFailed($e));
        }
    }
}
