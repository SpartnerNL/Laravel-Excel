<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithFreezePane;
use Maatwebsite\Excel\Tests\TestCase;

final class WithFreezePaneTest extends TestCase
{
    public function test_can_freeze_the_first_row(): void
    {
        $export = new class implements Export, FromArray, WithFreezePane
        {
            use Exportable;

            public function array(): array
            {
                return [['Patrick', 'Brouwers']];
            }

            public function freezePane(): string
            {
                return 'A2';
            }
        };

        $export->store('with-freeze-pane.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-freeze-pane.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertSame('A2', $sheet->getFreezePane());
    }

    public function test_can_freeze_both_row_and_column(): void
    {
        $export = new class implements Export, FromArray, WithFreezePane
        {
            use Exportable;

            public function array(): array
            {
                return [['Patrick', 'Brouwers']];
            }

            public function freezePane(): string
            {
                return 'B2';
            }
        };

        $export->store('with-freeze-pane.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-freeze-pane.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertSame('B2', $sheet->getFreezePane());
    }
}
