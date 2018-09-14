<?php

namespace Maatwebsite\Excel\Jobs;

use Maatwebsite\Excel\Writer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QueueImport implements ShouldQueue
{
    use ExtendedQueueable, Dispatchable;

    public function handle()
    {
        //
    }
}
