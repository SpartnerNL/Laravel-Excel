<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Tests\TestCase;

class AfterQueueExportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $filePath,
    ) {
    }

    public function handle(): void
    {
        TestCase::assertFileExists($this->filePath);
    }
}
