<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithChunkEvents;

class QueuedExportWithChunkEventsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__ . '/Data/Stubs/Database/Factories');
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Data/Stubs/Database/Migrations');

        factory(User::class)->times(100)->create([]);
    }

    /**
     * @test
     */
    public function can_queue_an_export()
    {
        $export = new QueuedExportWithChunkEvents();

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);

        // Counting created groups, because the export has event callbacks
        // which will create groups named 'before' and 'after' for
        // BeforeChunk and AfterChunk events
        $beforeCount = Group::query()->where('name', 'before')->count();
        $afterCount  = Group::query()->where('name', 'after')->count();
        $this->assertSame(10, $beforeCount);
        $this->assertSame(10, $afterCount);
    }
}