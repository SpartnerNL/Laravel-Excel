<?php

namespace Seoperin\LaravelExcel\Tests\Concerns;

use Illuminate\Support\Collection;
use Seoperin\LaravelExcel\Tests\TestCase;
use Seoperin\LaravelExcel\Concerns\Exportable;
use Seoperin\LaravelExcel\Concerns\WithHeadings;
use Seoperin\LaravelExcel\Concerns\FromCollection;
use Seoperin\LaravelExcel\Concerns\WithStrictNullComparison;

class WithStrictNullComparisonTest extends TestCase
{
    /**
     * @test
     */
    public function exported_zero_values_are_not_null_when_exporting_with_strict_null_comparison()
    {
        $export = new class implements FromCollection, WithHeadings, WithStrictNullComparison {
            use Exportable;

            /**
             * @return Collection
             */
            public function collection()
            {
                return collect([
                    ['string', '0', 0, 0.0, 'string'],
                ]);
            }

            /**
             * @return array
             */
            public function headings(): array
            {
                return ['string', '0', 0, 0.0, 'string'];
            }
        };

        $response = $export->store('with-strict-null-comparison-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-strict-null-comparison-store.xlsx', 'Xlsx');

        $expected = [
            ['string', 0.0, 0.0, 0.0, 'string'],
            ['string', 0.0, 0.0, 0.0, 'string'],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function exported_zero_values_are_null_when_not_exporting_with_strict_null_comparison()
    {
        $export = new class implements FromCollection, WithHeadings {
            use Exportable;

            /**
             * @return Collection
             */
            public function collection()
            {
                return collect([
                    ['string', 0, 0.0, 'string'],
                ]);
            }

            /**
             * @return array
             */
            public function headings(): array
            {
                return ['string', 0, 0.0, 'string'];
            }
        };

        $response = $export->store('without-strict-null-comparison-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/without-strict-null-comparison-store.xlsx', 'Xlsx');

        $expected = [
            ['string', null, null, 'string'],
            ['string', null, null, 'string'],
        ];

        $this->assertEquals($expected, $actual);
    }
}
