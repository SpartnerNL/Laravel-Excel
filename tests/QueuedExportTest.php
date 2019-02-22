<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Jobs\QueueExport;
use Illuminate\Queue\Events\JobProcessed;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\ShouldQueueExport;
use Maatwebsite\Excel\Tests\Data\Stubs\AfterQueueExportJob;
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
        config()->set('excel.remote_temp_disk', 'test');

        // Delete the local temp file after the QueueExport job
        // to simulate the following jobs using a different filesystem.
        Queue::after(function (JobProcessed $event) {
            if ($event->job->resolveName() === QueueExport::class) {
                $tempFile = $this->inspectJobProperty($event->job, 'tempFile');
                $this->assertTrue(unlink(config('excel.temp_path') . DIRECTORY_SEPARATOR . $tempFile));
            }
        });

        $export = new QueuedExport();

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__ . '/Data/Disks/Local/queued-export.xlsx'),
        ]);
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
}
