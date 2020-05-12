<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Files\RemoteTemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\ReadChunk;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueImportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedImport;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedImportWithFailure;
use Throwable;

class QueuedImportTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(__DIR__ . '/Data/Stubs/Database/Migrations');
    }

    /**
     * @test
     */
    public function cannot_queue_import_that_does_not_implement_should_queue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Importable should implement ShouldQueue to be queued.');

        $import = new class {
            use Importable;
        };

        $import->queue('import-batches.xlsx');
    }

    /**
     * @test
     */
    public function can_queue_an_import()
    {
        $import = new QueuedImport();

        $chain = $import->queue('import-batches.xlsx')->chain([
            new AfterQueueImportJob(5000),
        ]);

        $this->assertInstanceOf(PendingDispatch::class, $chain);
    }

    /**
     * @test
     */
    public function can_queue_import_with_remote_temp_disk()
    {
        config()->set('excel.temporary_files.remote_disk', 'test');

        // Delete the local temp file before each read chunk job
        // to simulate using a shared remote disk, without
        // having a dependency on a local temp file.
        Queue::before(function (JobProcessing $event) {
            if ($event->job->resolveName() === ReadChunk::class) {
                /** @var TemporaryFile $tempFile */
                $tempFile = $this->inspectJobProperty($event->job, 'temporaryFile');

                $this->assertInstanceOf(RemoteTemporaryFile::class, $tempFile);

                // Should exist remote
                $this->assertTrue(
                    $tempFile->exists()
                );

                $this->assertTrue(
                    unlink($tempFile->getLocalPath())
                );
            }
        });

        $import = new QueuedImport();

        $chain = $import->queue('import-batches.xlsx')->chain([
            new AfterQueueImportJob(5000),
        ]);

        $this->assertInstanceOf(PendingDispatch::class, $chain);
    }

    /**
     * @test
     */
    public function can_keep_extension_for_temp_file_on_remote_disk()
    {
        config()->set('excel.temporary_files.remote_disk', 'test');

        Queue::before(function (JobProcessing $event) {
            if ($event->job->resolveName() === ReadChunk::class) {
                /** @var TemporaryFile $tempFile */
                $tempFile = $this->inspectJobProperty($event->job, 'temporaryFile');

                $this->assertStringContains('.xlsx', $tempFile->getLocalPath());
            }
        });
        (new QueuedImport())->queue('import-batches.xlsx');
    }

    /**
     * @test
     */
    public function can_queue_import_with_remote_temp_disk_and_prefix()
    {
        config()->set('excel.temporary_files.remote_disk', 'test');
        config()->set('excel.temporary_files.remote_prefix', 'tmp/');

        $import = new QueuedImport();

        $chain = $import->queue('import-batches.xlsx')->chain([
            new AfterQueueImportJob(5000),
        ]);

        $this->assertInstanceOf(PendingDispatch::class, $chain);
    }

    /**
     * @test
     */
    public function can_automatically_delete_temp_file_on_failure()
    {
        $tempFile = '';

        Queue::exceptionOccurred(function (JobExceptionOccurred $event) use (&$tempFile) {
            if ($event->job->resolveName() === ReadChunk::class) {
                $tempFile = $this->inspectJobProperty($event->job, 'temporaryFile');
            }
        });

        try {
            (new QueuedImportWithFailure())->queue('import-batches.xlsx');
        } catch (Throwable $e) {
            $this->assertEquals('Something went wrong in the chunk', $e->getMessage());
        }

        $this->assertFalse($tempFile->exists());
    }
}
