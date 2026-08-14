<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTabColor;
use Maatwebsite\Excel\Tests\TestCase;

final class WithTabColorTest extends TestCase
{
    public function test_can_set_tab_color(): void
    {
        $export = new class implements Export, FromArray, WithTabColor
        {
            use Exportable;

            public function array(): array
            {
                return [['Patrick', 'Brouwers']];
            }

            public function tabColor(): string
            {
                return 'FF0000';
            }
        };

        $export->store('with-tab-color.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-tab-color.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertSame('FFFF0000', $sheet->getTabColor()->getARGB());
    }
}
