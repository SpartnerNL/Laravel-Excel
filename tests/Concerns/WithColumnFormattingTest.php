<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Carbon\Carbon;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class WithColumnFormattingTest extends TestCase
{
    public function test_can_export_with_column_formatting()
    {
        $export = new class() implements FromCollection, WithMapping, WithColumnFormatting
        {
            use Exportable;

            /**
             * @return Collection
             */
            public function collection()
            {
                return collect([
                    [Carbon::createFromDate(2018, 3, 6)],
                    [Carbon::createFromDate(2018, 3, 7)],
                    [Carbon::createFromDate(2018, 3, 8)],
                    [Carbon::createFromDate(2021, 12, 6), 100],
                ]);
            }

            /**
             * @param  mixed  $row
             * @return array
             */
            public function map($row): array
            {
                return [
                    Date::dateTimeToExcel($row[0]),
                    isset($row[1]) ? $row[1] : null,
                ];
            }

            /**
             * @return array
             */
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

        $legacyPhpSpreadsheet = !InstalledVersions::satisfies(new VersionParser, 'phpoffice/phpspreadsheet', '^1.28');

        $expected = [
            ['06/03/2018', null],
            ['07/03/2018', null],
            ['08/03/2018', null],
            ['06/12/2021', $legacyPhpSpreadsheet ? '100 €' : '100.00 €'],
        ];

        $this->assertEquals($expected, $actual);
    }
}
