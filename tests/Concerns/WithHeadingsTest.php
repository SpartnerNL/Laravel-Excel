<?php

namespace Seoperin\LaravelExcel\Tests\Concerns;

use Illuminate\Support\Collection;
use Seoperin\LaravelExcel\Tests\TestCase;
use Seoperin\LaravelExcel\Concerns\Exportable;
use Seoperin\LaravelExcel\Concerns\WithHeadings;
use Seoperin\LaravelExcel\Concerns\FromCollection;
use Seoperin\LaravelExcel\Concerns\WithCustomStartCell;

class WithHeadingsTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_from_collection_with_heading_row()
    {
        $export = new class implements FromCollection, WithHeadings {
            use Exportable;

            /**
             * @return Collection
             */
            public function collection()
            {
                return collect([
                    ['A1', 'B1', 'C1'],
                    ['A2', 'B2', 'C2'],
                ]);
            }

            /**
             * @return array
             */
            public function headings(): array
            {
                return ['A', 'B', 'C'];
            }
        };

        $response = $export->store('with-heading-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-heading-store.xlsx', 'Xlsx');

        $expected = [
            ['A', 'B', 'C'],
            ['A1', 'B1', 'C1'],
            ['A2', 'B2', 'C2'],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function can_export_from_collection_with_multiple_heading_rows()
    {
        $export = new class implements FromCollection, WithHeadings {
            use Exportable;

            /**
             * @return Collection
             */
            public function collection()
            {
                return collect([
                    ['A1', 'B1', 'C1'],
                    ['A2', 'B2', 'C2'],
                ]);
            }

            /**
             * @return array
             */
            public function headings(): array
            {
                return [
                    ['A', 'B', 'C'],
                    ['Aa', 'Bb', 'Cc'],
                ];
            }
        };

        $response = $export->store('with-heading-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-heading-store.xlsx', 'Xlsx');

        $expected = [
            ['A', 'B', 'C'],
            ['Aa', 'Bb', 'Cc'],
            ['A1', 'B1', 'C1'],
            ['A2', 'B2', 'C2'],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function can_export_from_collection_with_heading_row_with_custom_start_cell()
    {
        $export = new class implements FromCollection, WithHeadings, WithCustomStartCell {
            use Exportable;

            /**
             * @return Collection
             */
            public function collection()
            {
                return collect([
                    ['A1', 'B1', 'C1'],
                    ['A2', 'B2', 'C2'],
                ]);
            }

            /**
             * @return array
             */
            public function headings(): array
            {
                return ['A', 'B', 'C'];
            }

            /**
             * @return string
             */
            public function startCell(): string
            {
                return 'B2';
            }
        };

        $response = $export->store('with-heading-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-heading-store.xlsx', 'Xlsx');

        $expected = [
            [null, null, null, null],
            [null, 'A', 'B', 'C'],
            [null, 'A1', 'B1', 'C1'],
            [null, 'A2', 'B2', 'C2'],
        ];

        $this->assertEquals($expected, $actual);
    }
}
