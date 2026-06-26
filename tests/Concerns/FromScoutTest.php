<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersScoutExport;
use Maatwebsite\Excel\Tests\TestCase;

class FromScoutTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Data/Stubs/Database/Migrations');

        User::factory()->has(Group::factory(['name' => 'Group 1']))->count(100)->create();
        User::factory()->has(Group::factory(['name' => 'Group 2']))->count(5)->create();
    }

    public function test_can_export_from_scout(): void
    {
        $export = new FromUsersScoutExport;

        $response = $export->store('from-scout-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-scout-store.xlsx', 'Xlsx');

        $allUsers = $export->scout()->get()->map(fn (User $user) => array_values($user->toArray()))->toArray();

        $this->assertEquals($allUsers, $contents);
    }
}
