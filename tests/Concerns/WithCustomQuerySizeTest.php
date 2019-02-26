<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\FromQueryWithCustomQuerySize;

class WithCustomQuerySizeTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Data/Stubs/Database/Migrations');
        $this->withFactories(dirname(__DIR__) . '/Data/Stubs/Database/Factories');

        factory(Group::class)->times(5)->create()->each(function ($group) {
            $group->users()->attach(factory(User::class)->times(rand(1, 3))->create());
        });

        config()->set('excel.exports.chunk_size', 2);
    }

    /**
     * @test
     */
    public function can_export_with_custom_count()
    {
        $export = new FromQueryWithCustomQuerySize();

        $export->queue('export-from-query-with-count.xlsx', null, 'Xlsx')->chain([
            new AfterQueueExportJob(dirname(__DIR__) . '/Data/Disks/Local/export-from-query-with-count.xlsx'),
        ]);

        $actual = $this->readAsArray(dirname(__DIR__) . '/Data/Disks/Local/export-from-query-with-count.xlsx', 'Xlsx');

        $this->assertCount(Group::count(), $actual);
    }
}
