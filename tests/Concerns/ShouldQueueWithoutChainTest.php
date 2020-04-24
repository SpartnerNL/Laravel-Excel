<?php

use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Jobs\AfterImportJob;
use Maatwebsite\Excel\Jobs\QueueImport;
use Maatwebsite\Excel\Jobs\ReadChunk;
use Maatwebsite\Excel\Tests\Data\Stubs\QueueImportWithoutJobChaining;
use Maatwebsite\Excel\Tests\TestCase;

class ShouldQueueWithoutChainTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Data/Stubs/Database/Migrations');
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks()
    {
        DB::connection()->enableQueryLog();

        $import = new QueueImportWithoutJobChaining();
        $import->import('import-users.xlsx');

        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_to_model_without_job_chaining()
    {
        Queue::fake();

        $import = new QueueImportWithoutJobChaining();
        $import->import('import-users.xlsx');

        Queue::assertPushed(ReadChunk::class, 2);
        Queue::assertPushed(AfterImportJob::class, 1);
        Queue::assertPushed(AfterImportJob::class, function ($import) {
            return !is_null($import->delay);
        });
        Queue::assertNotPushed(QueueImport::class);
    }

    /**
     * @test
     */
    public function a_queue_name_can_be_specified_when_importing()
    {
        Queue::fake();

        $import        = new QueueImportWithoutJobChaining();
        $import->queue = 'queue-name';

        $import->import('import-users.xlsx');

        Queue::assertPushedOn('queue-name', ReadChunk::class);
        Queue::assertPushedOn('queue-name', AfterImportJob::class);
    }
}
