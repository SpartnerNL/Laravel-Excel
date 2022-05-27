<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class QueueImport implements ShouldQueue
{
    use Queueable, Dispatchable, Batchable, InteractsWithQueue;

    /**
     * @var int
     */
    public $tries;

    /**
     * @var int
     */
    public $timeout;

    /**
     * @param  ShouldQueue  $import
     */
    public function __construct(ShouldQueue $import = null)
    {
        if ($import) {
            $this->timeout = $import->timeout ?? null;
            $this->tries   = $import->tries ?? null;
        }
    }

    public function handle()
    {
        if(!empty($this->batch())) {

            if ($this->batch()->cancelled()) {
                // Determine if the batch has been cancelled...

                return;
            }

        }
    }
}
