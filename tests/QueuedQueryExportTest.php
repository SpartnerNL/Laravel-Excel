<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryExport;

class QueuedQueryExportTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__ . '/Data/Stubs/Database/Factories');

        factory(User::class)->times(100)->create([]);
    }

    /**
     * @test
     */
    public function can_queue_an_export()
    {
        $export = new FromUsersQueryExport();

        $export->queue('queued-query-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-query-export.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-query-export.xlsx', 'Xlsx');

        $this->assertCount(100, $actual);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('excel.exports.chunk_size', 10);
    }
}
