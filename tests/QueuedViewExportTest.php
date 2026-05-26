<?php

declare(strict_types=1);

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
    }

    public function test_can_queue_an_export(): void
    {
        $users  = User::factory()->count(100)->create();
        $export = new SheetForUsersFromView($users);

        $export->queue('queued-view-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-view-export.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-view-export.xlsx', 'Xlsx');

        $this->assertCount(101, $actual);
    }

    public function test_can_export_multiple_sheets_from_view(): void
    {
        /** @var Collection|User[] $users */
        $users = User::factory()->count(300)->create();

        $export = new FromViewExportWithMultipleSheets($users);

        $export->queue('queued-multiple-view-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-multiple-view-export.xlsx'),
        ]);

        $contents = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-multiple-view-export.xlsx', 'Xlsx', 0);

        $expected = $users->forPage(1, 100)->map(fn (User $user) => [
            $user->name,
            $user->email,
        ])->prepend(['Name', 'Email'])->toArray();

        $this->assertEquals(101, count($contents));
        $this->assertEquals($expected, $contents);

        $contents = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-multiple-view-export.xlsx', 'Xlsx', 2);

        $expected = $users->forPage(3, 100)->map(fn (User $user) => [
            $user->name,
            $user->email,
        ])->prepend(['Name', 'Email'])->toArray();

        $this->assertEquals(101, count($contents));
        $this->assertEquals($expected, $contents);
    }
}
