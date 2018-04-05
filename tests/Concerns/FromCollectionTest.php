<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\SheetWith100Rows;

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
            $spreadsheet = $this->read(
                __DIR__ . '/../Data/Disks/Local/multiple-sheets-collection-store.xlsx',
                'Xlsx'
            );

            $worksheet = $spreadsheet->getSheet($sheetIndex);

            $this->assertEquals($sheet->collection()->toArray(), $worksheet->toArray());
            $this->assertEquals($sheet->title(), $worksheet->getTitle());
        }
    }

    /**
     * @test
     */
    public function empty_rows_in_collection_will_be_ignored()
    {
        $export = new class implements FromCollection {
            use Exportable;

            /**
             * @return Collection
             */
            public function collection()
            {
                return new Collection([
                    [],
                    ['test', 'test'],
                    [],
                    ['test', 'test'],
                ]);
            }
        };

        $response = $export->store('from-collection-empty-rows-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-collection-empty-rows-store.xlsx', 'Xlsx');

        $this->assertEquals([
            ['test', 'test'],
            ['test', 'test'],
        ], $contents);
    }
}
