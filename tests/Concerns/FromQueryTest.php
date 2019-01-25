<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryExport;
use Maatwebsite\Excel\Tests\Data\Stubs\FromNonEloquentQueryExport;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryExportWithEagerLoad;

class FromQueryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Data/Stubs/Database/Migrations');

        $group = factory(Group::class)->create([
            'name' => 'Group 1',
        ]);

        factory(User::class)->times(100)->create()->each(function (User $user) use ($group) {
            $user->groups()->save($group);
        });
    }

    /**
     * @test
     */
    public function can_export_from_query()
    {
        $export = new FromUsersQueryExport;

        $response = $export->store('from-query-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(function (User $user) {
            return array_values($user->toArray());
        })->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    /**
     * @test
     */
    public function can_export_from_query_with_eager_loads()
    {
        DB::connection()->enableQueryLog();
        $export = new FromUsersQueryExportWithEagerLoad();

        $response = $export->store('from-query-store-with-eager-loads.xlsx');

        $this->assertTrue($response);

        // Should be 2 queries:
        // 1) select all users
        // 2) eager load query for groups
        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store-with-eager-loads.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(function (User $user) use ($export) {
            return $export->map($user);
        })->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    /**
     * @test
     */
    public function can_export_from_query_with_eager_loads_and_queued()
    {
        DB::connection()->enableQueryLog();
        $export = new FromUsersQueryExportWithEagerLoad();

        $export->queue('from-query-store-with-eager-loads.xlsx');

        // Should be 3 queries:
        // 1) Count users to create chunked queues
        // 2) select all users
        // 3) eager load query for groups
        $this->assertCount(3, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store-with-eager-loads.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(function (User $user) use ($export) {
            return $export->map($user);
        })->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    /**
     * @test
     */
    public function can_export_from_query_builder_without_using_eloquent()
    {
        $export = new FromNonEloquentQueryExport();

        $response = $export->store('from-query-store-without-eloquent.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store-without-eloquent.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(function ($row) {
            return array_values((array) $row);
        })->all();

        $this->assertEquals($allUsers, $contents);
    }

    /**
     * @test
     */
    public function can_export_from_query_builder_without_using_eloquent_and_queued()
    {
        $export = new FromNonEloquentQueryExport();

        $export->queue('from-query-store-without-eloquent.xlsx');

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store-without-eloquent.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(function ($row) {
            return array_values((array) $row);
        })->all();

        $this->assertEquals($allUsers, $contents);
    }
}
