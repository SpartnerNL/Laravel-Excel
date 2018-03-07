<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Tests\Data\Stubs\WithMappingExport;
use Maatwebsite\Excel\Tests\Data\Stubs\WithTitleExport;
use Maatwebsite\Excel\Tests\TestCase;

class WithTitleTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_with_title()
    {
        $export = new WithTitleExport();

        $response = $export->store('with-title-store.xlsx');

        $this->assertTrue($response);

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-title-store.xlsx', 'Xlsx');

        $this->assertEquals('given-title', $spreadsheet->getProperties()->getTitle());
        $this->assertEquals('given-title', $spreadsheet->getActiveSheet()->getTitle());
    }
}