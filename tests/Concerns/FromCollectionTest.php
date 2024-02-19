<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Tests\Data\Stubs\EloquentLazyCollectionExport;
use Maatwebsite\Excel\Tests\Data\Stubs\EloquentLazyCollectionQueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\SheetWith100Rows;
use Maatwebsite\Excel\Tests\TestCase;

class FromCollectionTest extends TestCase
{
    public function test_can_export_from_collection()
    {
        $export = new SheetWith100Rows('A');

        $response = $export->store('from-collection-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-collection-store.xlsx', 'Xlsx');

        $this->assertEquals($export->collection()->toArray(), $contents);
    }

    public function test_can_export_with_multiple_sheets_from_collection()
    {
        $export = new QueuedExport();

        $response = $export->store('multiple-sheets-collection-store.xlsx');

        $this->assertTrue($response);

        foreach ($export->sheets() as $sheetIndex => $sheet) {
            $spreadsheet = $this->read(
                __DIR__ . '/../Data/Disks/Local/multiple-sheets-collection-store.xlsx',
                'Xlsx'
            );

            $worksheet = $spreadsheet->getSheet($sheetIndex);

            $this->assertEquals($sheet->collection()->toArray(), $worksheet->toArray());
            $this->assertEquals($sheet->title(), $worksheet->getTitle());
        }
    }

    public function test_can_export_from_lazy_collection()
    {
        if (!class_exists('\Illuminate\Support\LazyCollection')) {
            $this->markTestSkipped('Skipping test because LazyCollection is not supported');

            return;
        }

        $export = new EloquentLazyCollectionExport();

        $export->store('from-lazy-collection-store.xlsx');

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-lazy-collection-store.xlsx', 'Xlsx');

        $this->assertEquals(
            $export->collection()->map(
                function (array $item) {
                    return array_values($item);
                }
            )->toArray(),
            $contents
        );
    }

    public function test_can_export_from_lazy_collection_with_queue()
    {
        if (!class_exists('\Illuminate\Support\LazyCollection')) {
            $this->markTestSkipped('Skipping test because LazyCollection is not supported');

            return;
        }

        $export = new EloquentLazyCollectionQueuedExport();

        $response = $export->queue('from-lazy-collection-store.xlsx');

        $this->assertTrue($response instanceof PendingDispatch);

        // Force dispatching via __destruct.
        unset($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-lazy-collection-store.xlsx', 'Xlsx');

        $this->assertEquals(
            $export->collection()->map(
                function (array $item) {
                    return array_values($item);
                }
            )->toArray(),
            $contents
        );
    }
}
