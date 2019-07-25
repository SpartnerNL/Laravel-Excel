<?php

namespace Maatwebsite\Excel\Tests;

use Throwable;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Jobs\QueueExportClass;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\ShouldQueueExport;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithFailedHook;
use Maatwebsite\Excel\Tests\Data\Stubs\EloquentCollectionWithMappingExport;

class QueuedExportTest extends TestCase
{
    /**
     * @test
     */
    public function can_queue_an_export()
    {
        $export = new QueuedExport();

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);
    }

    /**
     * @test
     */
    public function can_queue_an_export_and_store_on_different_disk()
    {
        $export = new QueuedExport();

        $export->queue('queued-export.xlsx', 'test')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Test/queued-export.xlsx'),
        ]);
    }

    /**
     * @test
     */
    public function can_queue_export_with_remote_temp_disk()
    {
        config()->set('excel.temporary_files.remote_disk', 'test');

        $export = new QueuedExport();

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);

        $array = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-export.xlsx', Excel::XLSX);

        $this->assertCount(100, $array);
    }

    /**
     * @test
     */
    public function can_implicitly_queue_an_export()
    {
        $export = new ShouldQueueExport();

        $export->store('queued-export.xlsx', 'test')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Test/queued-export.xlsx'),
        ]);
    }

    /**
     * @test
     */
    public function can_queue_export_with_mapping_on_eloquent_models()
    {
        $export = new EloquentCollectionWithMappingExport();

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/queued-export.xlsx', 'Xlsx');

        $this->assertEquals([
            ['Patrick', 'Brouwers'],
        ], $actual);
    }

    /**
     * @test
     */
    public function can_catch_failures()
    {
        $export = new QueuedExportWithFailedHook();
        try {
            $export->queue('queued-export.xlsx');
        } catch (Throwable $e) {
        }

        $this->assertTrue(app('queue-has-failed'));
    }

    /**
     * @test
     */
    public function may_have_maximum_attempts()
    {
        $export = new ShouldQueueExport();

        Queue::fake();

        Queue::assertNothingPushed();

        $export->queue('queued-export.xlsx');

        Queue::assertPushed(QueueExportClass::class, 1);
        // Job should have no maximum attempts per default
        Queue::assertPushed(QueueExportClass::class, function ($job) {
            return $job->tries === null;
        });

        $tries = 3;
        $export->queue('queued-export.xlsx', null, null, null, $tries);

        // Job should be pushed twice
        Queue::assertPushed(QueueExportClass::class, 2);
        Queue::assertPushed(QueueExportClass::class, function ($job) use ($tries) {
            return $job->tries === 3;
        });
    }
}
