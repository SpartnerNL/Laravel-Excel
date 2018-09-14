<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Bus\Queueable;
use PHPUnit\Framework\Assert;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class AfterQueueImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * @var int
     */
    private $totalRows;

    /**
     * @param int $totalRows
     */
    public function __construct(int $totalRows)
    {
        $this->totalRows = $totalRows;
    }

    public function handle()
    {
        Assert::assertEquals($this->totalRows, DB::table('groups')->count('id'));
    }
}
