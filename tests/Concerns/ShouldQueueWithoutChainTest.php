<?php

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\SyncQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Jobs\AfterImportJob;
use Maatwebsite\Excel\Jobs\QueueImport;
use Maatwebsite\Excel\Jobs\ReadChunk;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedImportWithQueueAttribute;
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

    public function test_can_import_to_model_in_chunks(): void
    {
        DB::connection()->enableQueryLog();

        $import = new QueueImportWithoutJobChaining;
        $import->import('import-users.xlsx');

        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    public function test_can_import_to_model_without_job_chaining(): void
    {
        Queue::fake();

        $import = new QueueImportWithoutJobChaining;
        $import->import('import-users.xlsx');

        Queue::assertPushed(ReadChunk::class, 2);
        Queue::assertPushed(AfterImportJob::class, 1);
        Queue::assertPushed(AfterImportJob::class, fn ($import) => !is_null($import->delay));
        Queue::assertNotPushed(QueueImport::class);
    }

    public function test_a_queue_name_can_be_specified_when_importing(): void
    {
        Queue::fake();

        $import        = new QueueImportWithoutJobChaining;
        $import->queue = 'queue-name';

        $import->import('import-users.xlsx');

        Queue::assertPushedOn('queue-name', ReadChunk::class);
        Queue::assertPushedOn('queue-name', AfterImportJob::class);
    }

    public function test_a_queue_attribute_is_used_when_importing(): void
    {
        if (!class_exists(Illuminate\Queue\Attributes\Queue::class)) {
            $this->markTestSkipped('The #[Queue] attribute is not available on this Laravel version');
        }

        Queue::fake();

        $import = new QueuedImportWithQueueAttribute;
        $import->import('import-users.xlsx');

        Queue::assertPushedOn('excel-imports', ReadChunk::class);
        Queue::assertPushedOn('excel-imports', AfterImportJob::class);
    }

    public function test_the_cleanup_only_runs_when_all_jobs_are_done(): void
    {
        $fake = Queue::fake();

        $import = new QueueImportWithoutJobChaining;

        $import->import('import-users.xlsx');

        /** @var array<class-string, array<int, array<string, mixed>>> $jobs */
        $jobs   = Queue::pushedJobs(); // @phpstan-ignore staticMethod.notFound
        $chunks = collect($jobs[ReadChunk::class])->pluck('job');
        $chunks->each(function (ReadChunk $chunk): void {
            self::assertFalse(ReadChunk::isComplete($chunk->getUniqueId()));
        });
        self::assertCount(2, $chunks);
        $afterImport = $jobs[AfterImportJob::class][0]['job'];

        $fake = app(SyncQueue::class);
        $fake->setContainer(app());
        $fake->push($chunks->first());
        self::assertTrue(ReadChunk::isComplete($chunks->first()->getUniqueId()));
        self::assertFalse(ReadChunk::isComplete($chunks->last()->getUniqueId()));

        Event::listen(JobProcessed::class, function (JobProcessed $event): void {
            self::assertTrue($event->job->isReleased());
        });
        $fake->push($afterImport);
        Event::forget(JobProcessed::class);
        $fake->push($chunks->last());

        Event::listen(JobProcessed::class, function (JobProcessed $event): void {
            self::assertFalse($event->job->isReleased());
        });
        $fake->push($afterImport);
        Event::forget(JobProcessed::class);
    }
}
