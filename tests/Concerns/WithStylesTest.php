<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WithStylesTest extends TestCase
{
    public function test_can_configure_styles()
    {
        $export = new class implements FromArray, WithStyles
        {
            use Exportable;

            public function styles(Worksheet $sheet)
            {
                return [
                    1    => ['font' => ['italic' => true]],
                    'B2' => ['font' => ['bold' => true]],
                    'C'  => ['font' => ['size' => 16]],
                ];
            }

            public function array(): array
            {
                return [
                    ['A1', 'B1', 'C1'],
                    ['A2', 'B2', 'C2'],
                ];
            }
        };

        $export->store('with-styles.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-styles.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertTrue($sheet->getStyle('A1')->getFont()->getItalic());
        $this->assertTrue($sheet->getStyle('B1')->getFont()->getItalic());
        $this->assertTrue($sheet->getStyle('B2')->getFont()->getBold());
        $this->assertFalse($sheet->getStyle('A2')->getFont()->getBold());
        $this->assertEquals(16, $sheet->getStyle('C2')->getFont()->getSize());
    }
}
