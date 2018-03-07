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

        $response = $export->store('from-collection-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-collection-store.xlsx', 'Xlsx');

        $this->assertEquals($export->collection()->prepend($export->headings())->toArray(), $contents);
    }
}
