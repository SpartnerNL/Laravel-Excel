<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class QueueImport implements ShouldQueue
{
    use Dispatchable, ExtendedQueueable;

    public ?int $tries;

    public ?int $timeout;

    public function __construct(?ShouldQueue $import = null)
    {
        if ($import instanceof ShouldQueue) {
            $this->timeout = $import->timeout ?? null;
            $this->tries   = $import->tries ?? null;
        }
    }

    public function handle(): void
    {
        //
    }
}
