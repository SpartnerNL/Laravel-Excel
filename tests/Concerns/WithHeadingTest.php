<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Tests\Data\Stubs\WithHeadingExport;

class WithHeadingTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_from_collection()
    {
        $export = new WithHeadingExport();

        $response = $export->store('with-heading-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-heading-store.xlsx', 'Xlsx');

        $expected = [
            ['A', 'B', 'C'],
            ['A1', 'B1', 'C1',],
            ['A2', 'B2', 'C2',]
        ];

        $this->assertEquals($expected, $actual);
    }
}
