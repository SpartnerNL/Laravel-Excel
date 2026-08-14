<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithPageBreaks;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class WithPageBreaksTest extends TestCase
{
    public function test_can_add_page_breaks(): void
    {
        $export = new class implements Export, FromArray, WithPageBreaks
        {
            use Exportable;

            public function array(): array
            {
                return array_fill(0, 10, ['value']);
            }

            public function pageBreaks(): array
            {
                return [5, 8];
            }
        };

        $export->store('with-page-breaks.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-page-breaks.xlsx', 'Xlsx');
        $breaks      = $spreadsheet->getActiveSheet()->getRowBreaks();

        $this->assertArrayHasKey('A5', $breaks);
        $this->assertSame(Worksheet::BREAK_ROW, $breaks['A5']->getBreakType());
        $this->assertArrayHasKey('A8', $breaks);
        $this->assertSame(Worksheet::BREAK_ROW, $breaks['A8']->getBreakType());
    }
}
