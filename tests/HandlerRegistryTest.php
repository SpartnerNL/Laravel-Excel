<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\DummySourceExport;
use Maatwebsite\Excel\Tests\Data\Stubs\DummySourceHandler;

final class HandlerRegistryTest extends TestCase
{
    private const string FILENAME = 'custom-source-handler.xlsx';

    private const array EXPECTED = [
        ['Name', 'Age'],
        ['Alice', '30'],
        ['Bob', '25'],
        ['Carol', '35'],
    ];

    public function test_custom_source_handler_writes_rows_on_sync_export(): void
    {
        Excel::registerSourceHandler(new DummySourceHandler);

        (new DummySourceExport)->store(self::FILENAME);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/' . self::FILENAME, 'Xlsx');

        $this->assertSame(self::EXPECTED, $actual);
    }

    public function test_custom_source_handler_can_be_registered_by_class_name(): void
    {
        Excel::registerSourceHandler(DummySourceHandler::class);

        (new DummySourceExport)->store(self::FILENAME);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/' . self::FILENAME, 'Xlsx');

        $this->assertSame(self::EXPECTED, $actual);
    }

    public function test_custom_source_handler_writes_rows_on_queued_export(): void
    {
        Excel::registerSourceHandler(new DummySourceHandler);

        (new DummySourceExport)->queue(self::FILENAME)->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/' . self::FILENAME),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/' . self::FILENAME, 'Xlsx');

        $this->assertSame(self::EXPECTED, $actual);
    }

    public function test_queued_export_respects_chunk_size(): void
    {
        Excel::registerSourceHandler(new DummySourceHandler);

        $jobs = 0;
        Queue::before(function (JobProcessing $event) use (&$jobs): void {
            if ($event->job->resolveName() === AppendDataToSheet::class) {
                $jobs++;
            }
        });

        (new DummySourceExport)->queue(self::FILENAME)->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/' . self::FILENAME),
        ]);

        // 4 rows with default chunk_size of 1000 → 1 AppendDataToSheet job
        $this->assertSame(1, $jobs);
    }

    public function test_queued_export_chunks_into_multiple_jobs(): void
    {
        config()->set('excel.exports.chunk_size', 2);

        Excel::registerSourceHandler(new DummySourceHandler);

        $jobs = 0;
        Queue::before(function (JobProcessing $event) use (&$jobs): void {
            if ($event->job->resolveName() === AppendDataToSheet::class) {
                $jobs++;
            }
        });

        (new DummySourceExport)->queue(self::FILENAME)->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/' . self::FILENAME),
        ]);

        // 4 rows, chunk_size 2 → 2 AppendDataToSheet jobs
        $this->assertSame(2, $jobs);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/' . self::FILENAME, 'Xlsx');

        $this->assertSame(self::EXPECTED, $actual);
    }
}
