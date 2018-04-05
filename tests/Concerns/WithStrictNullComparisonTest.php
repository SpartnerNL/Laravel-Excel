<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Tests\TestCase;

class WithStrictNullComparisonTest extends TestCase
{
    /**
     * @test
     */
    public function exported_zero_values_are_not_null_when_exporting_with_strict_null_comparison()
    {
        $export = new class implements FromCollection, WithHeadings, WithStrictNullComparison
        {
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
        $export = new class implements FromCollection, WithHeadings
        {
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
