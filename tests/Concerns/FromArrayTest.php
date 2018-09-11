<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExport;
use Maatwebsite\Excel\Tests\Data\Stubs\SheetWith100Rows;

class FromArrayTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_from_array()
    {
        $export = new class implements FromArray
        {
            use Exportable;

            /**
             * @return array
             */
            public function array(): array
            {
                return [
                    ['test', 'test'],
                    ['test', 'test'],
                ];
            }
        };

        $response = $export->store('from-array-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-array-store.xlsx', 'Xlsx');

        $this->assertEquals($export->array(), $contents);
    }
}
