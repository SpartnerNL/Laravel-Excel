<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\SettingsProvider;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryExport;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryExportWithMapping;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersScoutExport;

class QueuedQueryExportTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__ . '/Data/Stubs/Database/Factories');

        factory(User::class)->times(100)->create([]);
    }

    public function test_can_queue_an_export()
    {
        $export = new FromUsersQueryExport();

        $export->queue('queued-query-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-query-export.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-query-export.xlsx', 'Xlsx');

        $this->assertCount(100, $actual);

        // 6 of the 7 columns in export, excluding the "hidden" password column.
        $this->assertCount(6, $actual[0]);
    }

    public function test_can_queue_an_export_with_batch_cache_and_file_store()
    {
        config()->set('queue.default', 'sync');
        config()->set('excel.cache.driver', 'batch');
        config()->set('excel.cache.illuminate.store', 'file');
        config()->set('excel.cache.batch.memory_limit', 80);

        // Reset the cache settings
        $this->app->make(SettingsProvider::class)->provide();

        $export = new FromUsersQueryExport();

        $export->queue('queued-query-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-query-export.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-query-export.xlsx', 'Xlsx');

        $this->assertCount(100, $actual);
    }

    public function test_can_queue_an_export_with_mapping()
    {
        $export = new FromUsersQueryExportWithMapping();

        $export->queue('queued-query-export-with-mapping.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-query-export-with-mapping.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-query-export-with-mapping.xlsx', 'Xlsx');

        $this->assertCount(100, $actual);

        // Only 1 column when using map()
        $this->assertCount(1, $actual[0]);
        $this->assertEquals(User::value('name'), $actual[0][0]);
    }

    public function test_can_queue_scout_export()
    {
        if (!class_exists('\Laravel\Scout\Engines\DatabaseEngine')) {
            $this->markTestSkipped('Laravel Scout is too old');

            return;
        }

        $export = new FromUsersScoutExport();

        $export->queue('queued-scout-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-scout-export.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-scout-export.xlsx', 'Xlsx');

        $this->assertCount(100, $actual);

        // 6 of the 7 columns in export, excluding the "hidden" password column.
        $this->assertCount(6, $actual[0]);
    }
}
