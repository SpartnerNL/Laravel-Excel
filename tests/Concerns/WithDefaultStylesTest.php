<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;

class WithDefaultStylesTest extends TestCase
{
    /**
     * @test
     */
    public function can_configure_default_styles()
    {
        $export = new class implements FromArray, WithDefaultStyles
        {
            use Exportable;

            public function defaultStyles(Style $defaultStyle)
            {
                return [
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'fff2f2f2'],
                    ],
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

        $export->store('with-default-styles.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-default-styles.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getDefaultStyle();

        $this->assertEquals(Fill::FILL_SOLID, $sheet->getFill()->getFillType());
        $this->assertEquals('fff2f2f2', $sheet->getFill()->getStartColor()->getARGB());
    }
}
