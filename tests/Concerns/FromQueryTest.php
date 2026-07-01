<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\FromGroupUsersQueuedQueryExport;
use Maatwebsite\Excel\Tests\Data\Stubs\FromNestedArraysQueryExport;
use Maatwebsite\Excel\Tests\Data\Stubs\FromNonEloquentQueryExport;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryExport;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryExportWithEagerLoad;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryExportWithPrepareRows;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryWithJoinExport;
use Maatwebsite\Excel\Tests\TestCase;

class FromQueryTest extends TestCase
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

    public function test_can_export_from_query(): void
    {
        $export = new FromUsersQueryExport;

        $response = $export->store('from-query-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(fn (User $user) => array_values($user->toArray()))->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    public function test_can_export_from_query_with_join(): void
    {
        $export = new FromUsersQueryWithJoinExport;

        $response = $export->store('from-query-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store.xlsx', 'Xlsx');

        $allUsers = $export->query->get()->map(fn (User $user) => array_values($user->toArray()))->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    public function test_can_export_from_relation_query_queued(): void
    {
        $export = new FromGroupUsersQueuedQueryExport;

        $export->queue('from-query-store.xlsx');

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(fn (User $row) => $export->map($row))->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    public function test_can_export_from_query_with_eager_loads(): void
    {
        DB::connection()->enableQueryLog();
        $export = new FromUsersQueryExportWithEagerLoad;

        $response = $export->store('from-query-with-eager-loads.xlsx');

        $this->assertTrue($response);

        // Should be 2 queries:
        // 1) select all users
        // 2) eager load query for groups
        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-with-eager-loads.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(fn (User $user) => $export->map($user))->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    public function test_can_export_from_query_with_eager_loads_and_queued(): void
    {
        DB::connection()->enableQueryLog();
        $export = new FromUsersQueryExportWithEagerLoad;

        $export->queue('from-query-with-eager-loads.xlsx');

        // Should be 3 queries:
        // 1) Count users to create chunked queues
        // 2) select all users
        // 3) eager load query for groups
        $this->assertCount(3, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-with-eager-loads.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(fn (User $user) => $export->map($user))->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    public function test_can_export_from_query_builder_without_using_eloquent(): void
    {
        $export = new FromNonEloquentQueryExport;

        $response = $export->store('from-query-without-eloquent.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-without-eloquent.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(fn ($row) => array_values((array) $row))->all();

        $this->assertEquals($allUsers, $contents);
    }

    public function test_can_export_from_query_builder_without_using_eloquent_and_queued(): void
    {
        $export = new FromNonEloquentQueryExport;

        $export->queue('from-query-without-eloquent.xlsx');

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-without-eloquent.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(fn ($row) => array_values((array) $row))->all();

        $this->assertEquals($allUsers, $contents);
    }

    public function test_can_export_from_query_builder_with_nested_arrays(): void
    {
        $export = new FromNestedArraysQueryExport;

        $response = $export->store('from-query-with-nested-arrays.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-with-nested-arrays.xlsx', 'Xlsx');

        $this->assertEquals($this->format_nested_arrays_expected_data($export->query()->get()), $contents);
    }

    public function test_can_export_from_query_builder_with_nested_arrays_queued(): void
    {
        $export = new FromNestedArraysQueryExport;

        $export->queue('from-query-with-nested-arrays.xlsx');

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-with-nested-arrays.xlsx', 'Xlsx');

        $this->assertEquals($this->format_nested_arrays_expected_data($export->query()->get()), $contents);
    }

    public function test_can_export_from_query_with_batch_caching(): void
    {
        config()->set('excel.cache.driver', 'batch');

        $export = new FromUsersQueryExport;

        $response = $export->store('from-query-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(fn (User $user) => array_values($user->toArray()))->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    public function test_can_export_from_query_with_prepare_rows(): void
    {
        $export = new FromUsersQueryExportWithPrepareRows;

        $response = $export->store('from-query-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store.xlsx', 'Xlsx');

        $allUsers = $export->query()->get()->map(function (User $user) {
            $user->name .= '_prepared_name';

            return array_values($user->toArray());
        })->toArray();

        $this->assertEquals($allUsers, $contents);
    }

    /**
     * @param  Collection<int, Group>  $groups
     * @return array<int, array<int, string>>
     */
    protected function format_nested_arrays_expected_data(Collection $groups): array
    {
        $expected = [];
        foreach ($groups as $group) {
            $group_row = [$group->name, ''];

            foreach ($group->users as $key => $user) {
                if ($key === 0) {
                    $group_row[1] = $user->email;
                    $expected[]   = $group_row;

                    continue;
                }

                $expected[] = ['', $user->email];
            }
        }

        return $expected;
    }
}
