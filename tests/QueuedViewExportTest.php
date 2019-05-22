<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\SheetForUsersFromView;

class QueuedViewExportTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__ . '/Data/Stubs/Database/Factories');
    }

    /**
     * @test
     */
    public function can_queue_an_export()
    {
        $users  = factory(User::class)->times(100)->create([]);
        $export = new SheetForUsersFromView($users);

        $export->queue('queued-view-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-view-export.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-view-export.xlsx', 'Xlsx');

        $this->assertCount(101, $actual);
    }
}
