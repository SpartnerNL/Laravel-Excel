<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class WithCustomValueBinderTest extends TestCase
{
    public function test_can_set_a_value_binder_on_export(): void
    {
        \Illuminate\Support\Facades\Date::setTestNow(new Carbon('2018-08-07 18:00:00'));

        $export = new class extends DefaultValueBinder implements FromCollection, WithCustomValueBinder
        {
            use Exportable;

            public function collection(): Collection
            {
                return collect([
                    [Carbon::now(), '10%'],
                ]);
            }

            /**
             * {@inheritdoc}
             */
            public function bindValue(Cell $cell, mixed $value): bool
            {
                // Handle percentage
                if (preg_match('/^\-?\d*\.?\d*\s?\%$/', (string) $value)) {
                    $cell->setValueExplicit(
                        (float) str_replace('%', '', $value) / 100,
                        DataType::TYPE_NUMERIC
                    );

                    $cell
                        ->getWorksheet()
                        ->getStyle($cell->getCoordinate())
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

                    return true;
                }

                // Handle Carbon dates
                if ($value instanceof Carbon) {
                    $cell->setValueExplicit(
                        Date::dateTimeToExcel($value),
                        DataType::TYPE_NUMERIC
                    );

                    $cell->getWorksheet()
                        ->getStyle($cell->getCoordinate())
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);

                    return true;
                }

                return parent::bindValue($cell, $value);
            }
        };

        $export->store('custom-value-binder-export.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/custom-value-binder-export.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        // Check if the cell has the Excel date
        $this->assertSame(Date::dateTimeToExcel(Carbon::now()), $sheet->getCell('A1')->getValue());

        // Check if formatted as datetime
        $this->assertSame(NumberFormat::FORMAT_DATE_DATETIME, $sheet->getCell('A1')->getStyle()->getNumberFormat()->getFormatCode());

        // Check if the cell has the converted percentage
        $this->assertEqualsWithDelta(0.1, $sheet->getCell('B1')->getValue(), PHP_FLOAT_EPSILON);

        // Check if formatted as percentage
        $this->assertSame(NumberFormat::FORMAT_PERCENTAGE_00, $sheet->getCell('B1')->getStyle()->getNumberFormat()->getFormatCode());
    }
}
