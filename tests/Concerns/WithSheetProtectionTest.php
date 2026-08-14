<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithSheetProtection;
use Maatwebsite\Excel\Tests\TestCase;

final class WithSheetProtectionTest extends TestCase
{
    public function test_can_protect_sheet_without_password(): void
    {
        $export = new class implements Export, FromArray, WithSheetProtection
        {
            use Exportable;

            public function array(): array
            {
                return [['Patrick', 'Brouwers']];
            }

            public function sheetProtection(): ?string
            {
                return null;
            }
        };

        $export->store('with-sheet-protection.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-sheet-protection.xlsx', 'Xlsx');
        $protection  = $spreadsheet->getActiveSheet()->getProtection();

        $this->assertTrue($protection->getSheet());
        $this->assertEmpty($protection->getPassword());
    }

    public function test_can_protect_sheet_with_password(): void
    {
        $export = new class implements Export, FromArray, WithSheetProtection
        {
            use Exportable;

            public function array(): array
            {
                return [['Patrick', 'Brouwers']];
            }

            public function sheetProtection(): string
            {
                return 'secret';
            }
        };

        $export->store('with-sheet-protection.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-sheet-protection.xlsx', 'Xlsx');
        $protection  = $spreadsheet->getActiveSheet()->getProtection();

        $this->assertTrue($protection->getSheet());
        $this->assertNotEmpty($protection->getPassword());
    }
}
