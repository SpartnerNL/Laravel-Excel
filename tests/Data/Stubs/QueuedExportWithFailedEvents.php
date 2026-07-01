<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Exception;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\BeforeExport;
use PHPUnit\Framework\Assert;
use Throwable;

class QueuedExportWithFailedEvents implements WithEvents, WithMultipleSheets
{
    use Exportable;

    /**
     * @return SheetWith100Rows[]
     */
    public function sheets(): array
    {
        return [
            new SheetWith100Rows('A'),
            new SheetWith100Rows('B'),
            new SheetWith100Rows('C'),
        ];
    }

    public function failed(Throwable $exception): void
    {
        Assert::assertSame('catch exception from QueueExport job', $exception->getMessage());

        app()->bind('queue-has-failed-from-queue-export-job', fn () => true);
    }

    public function registerEvents(): array
    {
        return [
            BeforeExport::class => function (): void {
                throw new Exception('catch exception from QueueExport job');
            },
        ];
    }
}
