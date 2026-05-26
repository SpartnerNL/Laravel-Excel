<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Illuminate\Bus\PendingBatch;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\RemoteTemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\EloquentCollectionWithMappingExport;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithFailedEvents;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithFailedHook;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithLocalePreferences;
use Maatwebsite\Excel\Tests\Data\Stubs\ShouldBatchExport;
use Maatwebsite\Excel\Tests\Data\Stubs\ShouldQueueExport;
use Throwable;

final class QueuedExportTest extends TestCase
{
    public function test_can_queue_an_export(): void
    {
        $export = new QueuedExport;

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);
    }

    public function test_can_batch_an_export(): void
    {
        $export = new ShouldBatchExport;

        $batch = $export->queue('batch-export.xlsx', 'test')->name('batch-export-name');

        $this->assertInstanceOf(PendingBatch::class, $batch);
        $this->assertEquals('batch-export-name', $batch->name);
        $this->assertCount(1, $batch->jobs);
    }

    public function test_can_queue_an_export_and_store_on_different_disk(): void
    {
        $export = new QueuedExport;

        $export->queue('queued-export.xlsx', 'test')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Test/queued-export.xlsx'),
        ]);
    }

    public function test_can_queue_export_with_remote_temp_disk(): void
    {
        config()->set('excel.temporary_files.remote_disk', 'test');

        // Delete the local temp file before each append job
        // to simulate using a shared remote disk, without
        // having a dependency on a local temp file.
        $jobs = 0;
        Queue::before(function (JobProcessing $event) use (&$jobs): void {
            if ($event->job->resolveName() === AppendDataToSheet::class) {
                /** @var TemporaryFile $tempFile */
                $tempFile = $this->inspectJobProperty($event->job, 'temporaryFile');

                $this->assertInstanceOf(RemoteTemporaryFile::class, $tempFile);

                // Should exist remote
                $this->assertTrue(
                    $tempFile->exists()
                );

                // File was deleted locally
                $this->assertFalse(
                    file_exists($tempFile->getLocalPath())
                );

                $jobs++;
            }
        });

        $export = new QueuedExport;

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);

        $array = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-export.xlsx', Excel::XLSX);

        $this->assertCount(100, $array);
        $this->assertSame(3, $jobs);
    }

    public function test_can_queue_export_with_remote_temp_disk_and_prefix(): void
    {
        config()->set('excel.temporary_files.remote_disk', 'test');
        config()->set('excel.temporary_files.remote_prefix', 'tmp/');

        $export = new QueuedExport;

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);
    }

    public function test_can_implicitly_queue_an_export(): void
    {
        $export = new ShouldQueueExport;

        $export->store('queued-export.xlsx', 'test')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Test/queued-export.xlsx'),
        ]);
    }

    public function test_can_queue_export_with_mapping_on_eloquent_models(): void
    {
        $export = new EloquentCollectionWithMappingExport;

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-export.xlsx', 'Xlsx');

        $this->assertSame([
            ['Patrick', 'Brouwers'],
        ], $actual);
    }

    public function test_can_catch_failures(): void
    {
        $export = new QueuedExportWithFailedHook;
        try {
            $export->queue('queued-export.xlsx');
        } catch (Throwable) {
        }

        $this->assertTrue(app('queue-has-failed'));
    }

    public function test_can_catch_failures_on_queue_export_job(): void
    {
        $export = new QueuedExportWithFailedEvents;

        try {
            $export->queue('queued-export.xlsx');
        } catch (Throwable) {
        }

        $this->assertTrue(app('queue-has-failed-from-queue-export-job'));
    }

    public function test_can_set_locale_on_queue_export_job(): void
    {
        $currentLocale = app()->getLocale();

        $export = new QueuedExportWithLocalePreferences('ru');

        $export->queue('queued-export.xlsx');

        $this->assertTrue(app('queue-has-correct-locale'));

        $this->assertEquals($currentLocale, app()->getLocale());
    }

    public function test_can_queue_export_not_flushing_the_cache(): void
    {
        config()->set('excel.cache.driver', 'illuminate');

        Cache::put('test', 'test');

        $export = new QueuedExport;

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);

        $array = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-export.xlsx', Excel::XLSX);
        $this->assertCount(100, $array);

        $this->assertEquals('test', Cache::get('test'));
    }
}
