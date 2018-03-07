<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\SheetWith100Rows;
use Maatwebsite\Excel\Tests\TestCase;

class FromCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_from_collection()
    {
        $export = new SheetWith100Rows('A');

        $response = $export->store('from-collection-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-collection-store.xlsx', 'Xlsx');

        $this->assertEquals($export->collection()->toArray(), $contents);
    }

    /**
     * @test
     */
    public function can_export_with_multiple_sheets_from_collection()
    {
        $export = new QueuedExport();

        $response = $export->store('multiple-sheets-collection-store.xlsx');

        $this->assertTrue($response);

        foreach ($export->sheets() as $sheetIndex => $sheet) {
            $contents = $this->readAsArray(
                __DIR__ . '/../Data/Disks/Local/multiple-sheets-collection-store.xlsx',
                'Xlsx',
                $sheetIndex
            );
            $this->assertEquals($sheet->collection()->toArray(), $contents);
        }
    }
}