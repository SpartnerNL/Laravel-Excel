<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\HandlerRegistry;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Sheet;
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

    private readonly HandlerRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new HandlerRegistry;
    }

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

        $this->assertSame(2, $jobs);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/' . self::FILENAME, 'Xlsx');

        $this->assertSame(self::EXPECTED, $actual);
    }

    public function test_find_sync_handler_returns_null_when_registry_is_empty(): void
    {
        $this->assertNotInstanceOf(SheetSourceHandler::class, $this->registry->findSyncHandler(new DummySourceExport));
    }

    public function test_find_queued_handler_returns_null_when_registry_is_empty(): void
    {
        $this->assertNotInstanceOf(QueuedSheetSourceHandler::class, $this->registry->findQueuedHandler(new DummySourceExport));
    }

    public function test_find_sync_handler_returns_matching_handler(): void
    {
        $handler = new DummySourceHandler;
        $this->registry->register($handler);

        $this->assertSame($handler, $this->registry->findSyncHandler(new DummySourceExport));
    }

    public function test_find_queued_handler_returns_matching_handler(): void
    {
        $handler = new DummySourceHandler;
        $this->registry->register($handler);

        $this->assertSame($handler, $this->registry->findQueuedHandler(new DummySourceExport));
    }

    public function test_find_sync_handler_returns_null_when_no_handler_matches(): void
    {
        $this->registry->register(new DummySourceHandler);

        $unhandled = new class implements Export
        {
        };

        $this->assertNotInstanceOf(SheetSourceHandler::class, $this->registry->findSyncHandler($unhandled));
    }

    public function test_find_queued_handler_returns_null_when_no_handler_matches(): void
    {
        $this->registry->register(new DummySourceHandler);

        $unhandled = new class implements Export
        {
        };

        $this->assertNotInstanceOf(QueuedSheetSourceHandler::class, $this->registry->findQueuedHandler($unhandled));
    }

    public function test_last_registered_sync_handler_takes_priority(): void
    {
        $export = new class implements Export
        {
        };

        $handler1 = new class implements SheetSourceHandler
        {
            public function canHandle(Export $sheetExport): bool
            {
                return true;
            }

            public function handle(Sheet $sheet, Export $sheetExport): void
            {
            }
        };

        $handler2 = new class implements SheetSourceHandler
        {
            public function canHandle(Export $sheetExport): bool
            {
                return true;
            }

            public function handle(Sheet $sheet, Export $sheetExport): void
            {
            }
        };

        $this->registry->register($handler1);
        $this->registry->register($handler2);

        $this->assertSame($handler2, $this->registry->findSyncHandler($export));
    }

    public function test_last_registered_queued_handler_takes_priority(): void
    {
        $export = new class implements Export
        {
        };

        $handler1 = new class implements QueuedSheetSourceHandler
        {
            public function canHandle(Export $sheetExport): bool
            {
                return true;
            }

            public function buildJobs(Export $sheetExport, TemporaryFile $temporaryFile, string $writerType, int $sheetIndex, Export $export, int $chunkSize): iterable
            {
                return [];
            }
        };

        $handler2 = new class implements QueuedSheetSourceHandler
        {
            public function canHandle(Export $sheetExport): bool
            {
                return true;
            }

            public function buildJobs(Export $sheetExport, TemporaryFile $temporaryFile, string $writerType, int $sheetIndex, Export $export, int $chunkSize): iterable
            {
                return [];
            }
        };

        $this->registry->register($handler1);
        $this->registry->register($handler2);

        $this->assertSame($handler2, $this->registry->findQueuedHandler($export));
    }

    public function test_class_name_string_is_resolved_for_sync_lookup(): void
    {
        $this->registry->register(DummySourceHandler::class);

        $this->assertInstanceOf(DummySourceHandler::class, $this->registry->findSyncHandler(new DummySourceExport));
    }

    public function test_class_name_string_is_resolved_for_queued_lookup(): void
    {
        $this->registry->register(DummySourceHandler::class);

        $this->assertInstanceOf(DummySourceHandler::class, $this->registry->findQueuedHandler(new DummySourceExport));
    }

    public function test_queued_only_handler_is_skipped_by_sync_lookup(): void
    {
        $export = new class implements Export
        {
        };

        $this->registry->register(new class implements QueuedSheetSourceHandler
        {
            public function canHandle(Export $sheetExport): bool
            {
                return true;
            }

            public function buildJobs(Export $sheetExport, TemporaryFile $temporaryFile, string $writerType, int $sheetIndex, Export $export, int $chunkSize): iterable
            {
                return [];
            }
        });

        $this->assertNotInstanceOf(SheetSourceHandler::class, $this->registry->findSyncHandler($export));
    }

    public function test_sync_only_handler_is_skipped_by_queued_lookup(): void
    {
        $export = new class implements Export
        {
        };

        $this->registry->register(new class implements SheetSourceHandler
        {
            public function canHandle(Export $sheetExport): bool
            {
                return true;
            }

            public function handle(Sheet $sheet, Export $sheetExport): void
            {
            }
        });

        $this->assertNotInstanceOf(QueuedSheetSourceHandler::class, $this->registry->findQueuedHandler($export));
    }
}
