<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Foundation\Bus\PendingDispatch;
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
        $export = new class {
            use Importable;
        };

        $export->queue('import-batches.xlsx');
    }

    /**
     * @test
     */
    public function can_queue_an_import()
    {
        $export = new QueuedImport();

        $chain = $export->queue('import-batches.xlsx')->chain([
            new AfterQueueImportJob(5000),
        ]);

        $this->assertInstanceOf(PendingDispatch::class, $chain);
    }
}
