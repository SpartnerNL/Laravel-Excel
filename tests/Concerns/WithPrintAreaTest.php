<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithPrintArea;
use Maatwebsite\Excel\Tests\TestCase;

final class WithPrintAreaTest extends TestCase
{
    public function test_can_set_print_area(): void
    {
        $export = new class implements Export, FromArray, WithPrintArea
        {
            use Exportable;

            public function array(): array
            {
                return [['Patrick', 'Brouwers']];
            }

            public function printArea(): string
            {
                return 'A1:B10';
            }
        };

        $export->store('with-print-area.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-print-area.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertSame('A1:B10', $sheet->getPageSetup()->getPrintArea());
    }
}
