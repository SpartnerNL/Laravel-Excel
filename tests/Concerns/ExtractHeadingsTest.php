<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ExtractHeadings;

class ExtractHeadingsTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ExtractHeadings is only supported in combination with WithHeadingRow.
     */
    public function cannot_extract_headings_from_an_import_that_does_not_implement_with_heading_row()
    {
        $export = new class implements ExtractHeadings {
            use Importable;
        };

        $export->import('import-users-with-headings.xlsx');
    }

    /** @test */
    public function can_extract_headings_from_a_file_with_one_worksheet()
    {
        $import = new class implements ExtractHeadings, WithHeadingRow {
            use Importable;
        };

        $result = $import->import('import-users-with-headings.xlsx');

        $this->assertCount(1, $result);
        $this->assertSame(['name', 'email'], $result->first()->toArray());
    }

    /** @test */
    public function can_extract_headings_from_a_file_with_multiple_worksheets()
    {
        $import = new class implements ExtractHeadings, WithHeadingRow {
            use Importable;
        };

        $result = $import->import('extract-headings-from-multiple-worksheets.xlsx');

        $this->assertCount(2, $result);
        $this->assertSame(['name', 'email'], $result->first()->toArray());
        $this->assertSame(['github', 'twitter'], $result->last()->toArray());
    }
}
