<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Illuminate\Bus\PendingBatch;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\Filesystem;
use Maatwebsite\Excel\Files\RemoteTemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Jobs\StoreQueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\EloquentCollectionWithMappingExport;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithCsvSettings;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithFailedEvents;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithFailedHook;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithLocalePreferences;
use Maatwebsite\Excel\Tests\Data\Stubs\ShouldBatchExport;
use Maatwebsite\Excel\Tests\Data\Stubs\ShouldQueueExport;
use RuntimeException;
use Throwable;

final class QueuedExportTest extends TestCase
{
    public function test_can_queue_an_export(): void
    {
        $export = new QueuedExport;

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);

        $this->assertCount(100, $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-export.xlsx', Excel::XLSX));
    }

    public function test_can_batch_an_export(): void
    {
        $export = new ShouldBatchExport;

        $batch = $export->queue('batch-export.xlsx', 'test')->name('batch-export-name');

        $this->assertInstanceOf(PendingBatch::class, $batch);
        $this->assertSame('batch-export-name', $batch->name);
        $this->assertCount(1, $batch->jobs);
    }

    public function test_can_queue_an_export_and_store_on_different_disk(): void
    {
        $export = new QueuedExport;

        $export->queue('queued-export.xlsx', 'test')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Test/queued-export.xlsx'),
        ]);

        $this->assertCount(100, $this->readAsArray(__DIR__ . '/Data/Disks/Test/queued-export.xlsx', Excel::XLSX));
    }

    public function test_fails_and_keeps_the_temporary_file_when_the_export_cannot_be_stored(): void
    {
        // The local disk does not throw on write errors, so writing
        // over this directory makes the write return false.
        $path = __DIR__ . '/Data/Disks/Local/unwritable-export.xlsx';
        mkdir($path);

        $failedJob     = null;
        $temporaryFile = null;
        Queue::failing(function (JobFailed $event) use (&$failedJob, &$temporaryFile): void {
            if ($event->job->resolveName() === StoreQueuedExport::class) {
                $failedJob     = unserialize($event->job->payload()['data']['command']);
                $temporaryFile = $this->inspectJobProperty($event->job, 'temporaryFile');
            }
        });

        try {
            $exception = null;

            try {
                (new QueuedExport)->queue('unwritable-export.xlsx');
            } catch (RuntimeException $e) {
                $exception = $e;
            } finally {
                rmdir($path);
            }

            $this->assertInstanceOf(RuntimeException::class, $exception);
            $this->assertSame('Unable to store the export at [unwritable-export.xlsx].', $exception->getMessage());
            $this->assertInstanceOf(StoreQueuedExport::class, $failedJob);
            $this->assertInstanceOf(TemporaryFile::class, $temporaryFile);
            $this->assertFileExists($temporaryFile->getLocalPath());

            // Retrying the job once the disk accepts the file stores the export.
            $failedJob->handle(app(Filesystem::class));

            $this->assertCount(100, $this->readAsArray($path, Excel::XLSX));
            $this->assertFileDoesNotExist($temporaryFile->getLocalPath());
        } finally {
            @unlink($path);
            $temporaryFile?->delete();
        }
    }

    public function test_retrying_a_stored_export_without_its_temporary_file_does_not_fail(): void
    {
        config()->set('excel.temporary_files.remote_disk', 'test');

        $storeJob = null;
        Queue::after(function (JobProcessed $event) use (&$storeJob): void {
            if ($event->job->resolveName() === StoreQueuedExport::class) {
                $storeJob = unserialize($event->job->payload()['data']['command']);
            }
        });

        (new QueuedExport)->queue('redelivered-export.xlsx');

        $this->assertInstanceOf(StoreQueuedExport::class, $storeJob);

        // A redelivered job runs again after the stored export
        // already deleted its temporary file.
        $storeJob->handle(app(Filesystem::class));

        $this->assertCount(100, $this->readAsArray(__DIR__ . '/Data/Disks/Local/redelivered-export.xlsx', Excel::XLSX));
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
                $this->assertFileDoesNotExist(
                    $tempFile->getLocalPath()
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

        // Start from a clean prefix, temporary files of previous
        // runs are not cleaned up in between test runs.
        Storage::disk('test')->deleteDirectory('tmp');

        // Capture the remote temporary files while the export is still running,
        // they are cleaned up by the time the export has finished.
        $prefixedFiles = [];
        Queue::before(function (JobProcessing $event) use (&$prefixedFiles): void {
            if ($event->job->resolveName() === AppendDataToSheet::class) {
                $prefixedFiles = Storage::disk('test')->files('tmp');
            }
        });

        $export = new QueuedExport;

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);

        $this->assertCount(1, $prefixedFiles);
        $this->assertCount(100, $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-export.xlsx', Excel::XLSX));
    }

    public function test_can_implicitly_queue_an_export(): void
    {
        $export = new ShouldQueueExport;

        $export->store('queued-export.xlsx', 'test')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Test/queued-export.xlsx'),
        ]);

        $this->assertCount(100, $this->readAsArray(__DIR__ . '/Data/Disks/Test/queued-export.xlsx', Excel::XLSX));
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

        $this->assertSame($currentLocale, app()->getLocale());
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

        $this->assertSame('test', Cache::get('test'));
    }

    public function test_queued_exports_apply_root_level_concerns(): void
    {
        // Root-level concerns (here: CSV settings) are applied by the writer at
        // write() time. In a multi-sheet queued export the per-sheet chunk jobs
        // must write using the root export, otherwise the root concern is dropped.
        $export = new QueuedExportWithCsvSettings;

        $path = __DIR__ . '/Data/Disks/Local/queued-export-with-csv-settings.csv';

        $export->queue('queued-export-with-csv-settings.csv', null, Excel::CSV)->chain([
            new AfterQueueExportJob($path),
        ]);

        $firstLine = strtok((string) file_get_contents($path), "\n");

        $this->assertStringContainsString(';', (string) $firstLine, 'Root custom CSV delimiter should survive a queued export');
    }
}
