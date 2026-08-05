<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Assert;

class AfterQueueImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $totalRows,
    ) {
    }

    public function handle(): void
    {
        Assert::assertSame($this->totalRows, DB::table('groups')->count('id'));
    }
}
