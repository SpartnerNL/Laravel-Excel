<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\AfterBatch;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Tests\Data\Stubs\BeforeExportListener;
use Maatwebsite\Excel\Tests\Data\Stubs\CustomConcern;
use Maatwebsite\Excel\Tests\Data\Stubs\CustomSheetConcern;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\ExportWithEvents;
use Maatwebsite\Excel\Tests\Data\Stubs\ExportWithEventsChunks;
use Maatwebsite\Excel\Tests\Data\Stubs\ImportWithEvents;
use Maatwebsite\Excel\Tests\Data\Stubs\ImportWithEventsChunksAndBatches;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Writer;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class WithEventsTest extends TestCase
{
    use WithFaker;

    public function test_export_events_get_called(): void
    {
        $event = new ExportWithEvents;

        $eventsTriggered = 0;

        $event->beforeExport = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
            $eventsTriggered++;
        };

        $event->beforeWriting = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeWriting::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
            $eventsTriggered++;
        };

        $event->beforeSheet = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $event->afterSheet = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
        $this->assertSame(4, $eventsTriggered);
    }

    public function test_import_events_get_called(): void
    {
        $import = new ImportWithEvents;

        $eventsTriggered = 0;

        $import->beforeImport = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->getReader());
            $eventsTriggered++;
        };

        $import->afterImport = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(AfterImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->getReader());
            $eventsTriggered++;
        };

        $import->beforeSheet = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $import->afterSheet = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $import->import('import.xlsx');
        $this->assertSame(4, $eventsTriggered);
    }

    public function test_import_chunked_events_get_called(): void
    {
        $import = new ImportWithEventsChunksAndBatches;

        $beforeImport = 0;
        $afterImport  = 0;
        $beforeSheet  = 0;
        $afterSheet   = 0;
        $afterBatch   = 0;
        $afterChunk   = 0;

        $import->beforeImport = function (BeforeImport $event) use (&$beforeImport): void {
            $this->assertInstanceOf(Reader::class, $event->getReader());
            // Ensure event is fired only once
            $this->assertSame(0, $beforeImport, 'Before import called twice');
            $beforeImport++;
        };

        $import->afterImport = function (AfterImport $event) use (&$afterImport): void {
            $this->assertInstanceOf(Reader::class, $event->getReader());
            $this->assertSame(0, $afterImport, 'After import called twice');
            $afterImport++;
        };

        $import->beforeSheet = function (BeforeSheet $event) use (&$beforeSheet): void {
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $beforeSheet++;
        };

        $import->afterSheet = function (AfterSheet $event) use (&$afterSheet): void {
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $afterSheet++;
        };

        $import->afterBatch = function (AfterBatch $event) use ($import, &$afterBatch): void {
            $this->assertSame(
                $import->batchSize(),
                $event->getBatchSize(),
                'Wrong Batch size'
            );
            $this->assertSame(
                $afterBatch * $import->batchSize() + 1,
                $event->getStartRow(),
                'Wrong batch start row'
            );
            $afterBatch++;
        };

        $import->afterChunk = function (AfterChunk $event) use ($import, &$afterChunk): void {
            $this->assertSame(
                $event->getStartRow(),
                $afterChunk * $import->chunkSize() + 1,
                'Wrong chunk start row'
            );
            $afterChunk++;
        };

        $import->import('import-batches.xlsx');
        $this->assertSame(10, $afterSheet);
        $this->assertSame(10, $beforeSheet);
        $this->assertSame(50, $afterBatch);
        $this->assertSame(10, $afterChunk);
    }

    public function test_after_chunk_event_sheet_delegate_is_accessible(): void
    {
        $import = new ImportWithEventsChunksAndBatches();

        $import->afterChunk = function (AfterChunk $event): void {
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $this->assertInstanceOf(Worksheet::class, $event->getSheet()->getDelegate());
        };

        $import->import('import-batches.xlsx');
    }

    public function test_can_have_invokable_class_as_listener(): void
    {
        $event = new ExportWithEvents;

        $event->beforeExport = new BeforeExportListener(function ($event): void {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
    }

    public function test_can_have_global_event_listeners(): void
    {
        $event = new class
        {
            use Exportable;
        };

        $beforeExport = false;
        Writer::listen(BeforeExport::class, function () use (&$beforeExport): void {
            $beforeExport = true;
        });

        $beforeWriting = false;
        Writer::listen(BeforeWriting::class, function () use (&$beforeWriting): void {
            $beforeWriting = true;
        });

        $beforeSheet = false;
        Sheet::listen(BeforeSheet::class, function () use (&$beforeSheet): void {
            $beforeSheet = true;
        });

        $afterSheet = false;
        Sheet::listen(AfterSheet::class, function () use (&$afterSheet): void {
            $afterSheet = true;
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));

        $this->assertTrue($beforeExport, 'Before export event not triggered');
        $this->assertTrue($beforeWriting, 'Before writing event not triggered');
        $this->assertTrue($beforeSheet, 'Before sheet event not triggered');
        $this->assertTrue($afterSheet, 'After sheet event not triggered');
    }

    public function test_can_have_custom_concern_handlers(): void
    {
        // Add a custom concern handler for the given concern.
        Excel::extend(CustomConcern::class, function (CustomConcern $exportable, Writer $writer): void {
            $writer->getSheetByIndex(0)->append(
                $exportable->custom()
            );
        });

        $exportWithConcern = new class implements CustomConcern
        {
            use Exportable;

            /**
             * @return list<list<string>>
             */
            public function custom(): array
            {
                return [
                    ['a', 'b'],
                ];
            }
        };

        $exportWithConcern->store('with-custom-concern.xlsx');
        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-custom-concern.xlsx', 'Xlsx');
        $this->assertSame([
            ['a', 'b'],
        ], $actual);

        $exportWithoutConcern = new class
        {
            use Exportable;
        };

        $exportWithoutConcern->store('without-custom-concern.xlsx');
        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/without-custom-concern.xlsx', 'Xlsx');

        $this->assertSame([[null]], $actual);
    }

    public function test_can_have_custom_sheet_concern_handlers(): void
    {
        // Add a custom concern handler for the given concern.
        Excel::extend(CustomSheetConcern::class, function (CustomSheetConcern $exportable, Sheet $sheet): void {
            $sheet->append(
                $exportable->custom()
            );
        }, AfterSheet::class);

        $exportWithConcern = new class implements CustomSheetConcern
        {
            use Exportable;

            /**
             * @return list<list<string>>
             */
            public function custom(): array
            {
                return [
                    ['c', 'd'],
                ];
            }
        };

        $exportWithConcern->store('with-custom-concern.xlsx');
        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-custom-concern.xlsx', 'Xlsx');
        $this->assertSame([
            ['c', 'd'],
        ], $actual);

        $exportWithoutConcern = new class
        {
            use Exportable;
        };

        $exportWithoutConcern->store('without-custom-concern.xlsx');
        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/without-custom-concern.xlsx', 'Xlsx');

        $this->assertSame([[null]], $actual);
    }

    public function test_export_chunked_events_get_called(): void
    {
        $this->loadLaravelMigrations(['--database' => 'testing']);

        User::query()->truncate();

        User::query()->create([
            'name'           => $this->faker->name,
            'email'          => $this->faker->unique()->safeEmail,
            'password'       => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'remember_token' => Str::random(10),
        ]);

        User::query()->create([
            'name'           => $this->faker->name,
            'email'          => $this->faker->unique()->safeEmail,
            'password'       => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'remember_token' => Str::random(10),
        ]);

        $export = new ExportWithEventsChunks;
        $export->queue('filename.xlsx');

        // Chunk size is 1, so we expect 2 chunks to be executed with a total of 2 users
        $this->assertSame(2, ExportWithEventsChunks::$calledEvent);
    }
}
