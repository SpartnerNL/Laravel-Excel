<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Assert;

class AfterQueueImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  int  $totalRows
     */
    public function __construct(private int $totalRows)
    {
    }

    public function handle()
    {
        Assert::assertEquals($this->totalRows, DB::table('groups')->count('id'));
    }
}
