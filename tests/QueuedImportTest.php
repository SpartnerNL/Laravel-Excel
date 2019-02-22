<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Jobs\ReadChunk;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Files\TemporaryFile;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Files\RemoteTemporaryFile;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedImport;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueImportJob;

class QueuedImportTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(__DIR__ . '/Data/Stubs/Database/Migrations');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Importable should implement ShouldQueue to be queued.
     */
    public function cannot_queue_import_that_does_not_implement_should_queue()
    {
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
        config()->set('excel.remote_temp_disk', 'test');

        Queue::before(function (JobProcessing $event) {
            if ($event->job->resolveName() === ReadChunk::class) {
                /** @var TemporaryFile $tempFile */
                $tempFile = $this->inspectJobProperty($event->job, 'temporaryFile');
                $this->assertInstanceOf(RemoteTemporaryFile::class, $tempFile);
            }
        });

        // Delete the local temp file before the first ReadChunk job
        // to simulate the job using a different filesystem.
        //$tempFileDeleted = false;
        //Queue::before(function (JobProcessing $event) use (&$tempFileDeleted) {
        //    if (!$tempFileDeleted && $event->job->resolveName() === ReadChunk::class) {
        //        $tempFile = $this->inspectJobProperty($event->job, 'fileName');
        //        $this->assertTrue(unlink(config('excel.temp_path') . DIRECTORY_SEPARATOR . $tempFile));
        //        $tempFileDeleted = true;
        //    }
        //});

        // Delete the local temp file after the QueueExport job
        // to simulate the following jobs using a different filesystem.
        Queue::after(function (JobProcessed $event) {
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
}
