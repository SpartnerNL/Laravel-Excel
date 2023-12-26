<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\FromViewExportWithMultipleSheets;
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

    /**
     * @test
     */
    public function can_export_multiple_sheets_from_view()
    {
        /** @var Collection|User[] $users */
        $users = factory(User::class)->times(300)->make();

        $export = new FromViewExportWithMultipleSheets($users);

        $export->queue('queued-multiple-view-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-multiple-view-export.xlsx'),
        ]);

        $contents = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-multiple-view-export.xlsx', 'Xlsx', 0);

        $expected = $users->forPage(1, 100)->map(function (User $user) {
            return [
                $user->name,
                $user->email,
            ];
        })->prepend(['Name', 'Email'])->toArray();

        $this->assertEquals(101, sizeof($contents));
        $this->assertEquals($expected, $contents);

        $contents = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-multiple-view-export.xlsx', 'Xlsx', 2);

        $expected = $users->forPage(3, 100)->map(function (User $user) {
            return [
                $user->name,
                $user->email,
            ];
        })->prepend(['Name', 'Email'])->toArray();

        $this->assertEquals(101, sizeof($contents));
        $this->assertEquals($expected, $contents);
    }
}
