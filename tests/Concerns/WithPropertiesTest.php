<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Tests\TestCase;

class WithPropertiesTest extends TestCase
{
    public function test_can_set_custom_document_properties(): void
    {
        $export = new class implements WithProperties
        {
            use Exportable;

            public function properties(): array
            {
                return [
                    'creator'        => 'A',
                    'lastModifiedBy' => 'B',
                    'title'          => 'C',
                    'description'    => 'D',
                    'subject'        => 'E',
                    'keywords'       => 'F',
                    'category'       => 'G',
                    'manager'        => 'H',
                    'company'        => 'I',
                ];
            }
        };

        $export->store('with-properties.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-properties.xlsx', 'Xlsx');
        $props       = $spreadsheet->getProperties();

        $this->assertSame('A', $props->getCreator());
        $this->assertSame('B', $props->getLastModifiedBy());
        $this->assertSame('C', $props->getTitle());
        $this->assertSame('D', $props->getDescription());
        $this->assertSame('E', $props->getSubject());
        $this->assertSame('F', $props->getKeywords());
        $this->assertSame('G', $props->getCategory());
        $this->assertSame('H', $props->getManager());
        $this->assertSame('I', $props->getCompany());
    }

    public function test_it_merges_with_default_properties(): void
    {
        config()->set('excel.exports.properties.title', 'Default Title');
        config()->set('excel.exports.properties.description', 'Default Description');

        $export = new class implements WithProperties
        {
            use Exportable;

            public function properties(): array
            {
                return [
                    'description' => 'Custom Description',
                ];
            }
        };

        $export->store('with-properties.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-properties.xlsx', 'Xlsx');
        $props       = $spreadsheet->getProperties();

        $this->assertSame('Default Title', $props->getTitle());
        $this->assertSame('Custom Description', $props->getDescription());
    }

    public function test_it_ignores_empty_properties(): void
    {
        $export = new class implements WithProperties
        {
            use Exportable;

            public function properties(): array
            {
                return [
                    'description' => '',
                ];
            }
        };

        $export->store('with-properties.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-properties.xlsx', 'Xlsx');
        $props       = $spreadsheet->getProperties();

        $this->assertSame('Unknown Creator', $props->getCreator());
        $this->assertSame('Untitled Spreadsheet', $props->getTitle());
        $this->assertSame('', $props->getDescription());
    }
}
