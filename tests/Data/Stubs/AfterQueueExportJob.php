<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Tests\TestCase;

class AfterQueueExportJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $filePath
     */
    public function __construct(private string $filePath)
    {
    }

    public function handle()
    {
        TestCase::assertFileExists($this->filePath);
    }
}
