<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class WithColumnFormattingTest extends TestCase
{
    public function test_can_export_with_column_formatting(): void
    {
        $export = new class implements FromCollection, WithColumnFormatting, WithMapping
        {
            use Exportable;

            public function collection(): Collection
            {
                return collect([
                    [Carbon::createFromDate(2018, 3, 6)],
                    [Carbon::createFromDate(2018, 3, 7)],
                    [Carbon::createFromDate(2018, 3, 8)],
                    [Carbon::createFromDate(2021, 12, 6), 100],
                ]);
            }

            public function map(mixed $row): array
            {
                return [
                    Date::dateTimeToExcel($row[0]),
                    $row[1] ?? null,
                ];
            }

            public function columnFormats(): array
            {
                return [
                    'A'     => NumberFormat::FORMAT_DATE_DDMMYYYY,
                    'B4:B4' => NumberFormat::FORMAT_CURRENCY_EUR,
                ];
            }
        };

        $response = $export->store('with-column-formatting-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-column-formatting-store.xlsx', 'Xlsx');

        $expected = [
            ['06/03/2018', null],
            ['07/03/2018', null],
            ['08/03/2018', null],
            ['06/12/2021', '100.00 €'],
        ];

        $this->assertSame($expected, $actual);
    }
}
