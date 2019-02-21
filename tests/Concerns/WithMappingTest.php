<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Tests\Data\Stubs\WithMappingExport;

class WithMappingTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_with_heading()
    {
        $export = new WithMappingExport();

        $response = $export->store('with-mapping-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-mapping-store.xlsx', 'Xlsx');

        $expected = [
            [
                'mapped-A1',
                'mapped-B1',
                'mapped-C1',
            ],
            [
                'mapped-A2',
                'mapped-B2',
                'mapped-C2',
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function can_return_multiple_rows_in_map()
    {
        $export = new class implements FromArray, WithMapping
        {

            use Exportable;

            /**
             * @return array
             */
            public function array(): array
            {
                return [
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3],
                ];
            }

            /**
             * @param mixed $row
             *
             * @return array
             */
            public function map($row): array
            {
                return [
                    [$row['id']],
                    [$row['id']]
                ];
            }
        };

        $response = $export->store('with-mapping-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-mapping-store.xlsx', 'Xlsx');

        $this->assertCount(6, $actual);
    }

    /**
     * @test
     */
    public function json_array_columns_shouldnt_be_detected_as_multiple_rows()
    {
        $export = new class implements FromArray
        {

            use Exportable;

            /**
             * @return array
             */
            public function array(): array
            {
                return [
                    ['id' => 1, 'json' => ['other_id' => 1]],
                    ['id' => 2, 'json' => ['other_id' => 2]],
                    ['id' => 3, 'json' => ['other_id' => 3]],
                ];
            }
        };

        $response = $export->store('with-mapping-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-mapping-store.xlsx', 'Xlsx');

        $this->assertCount(3, $actual);

        $this->assertEquals([
            [1, \json_encode(['other_id' => 1])],
            [2, \json_encode(['other_id' => 2])],
            [3, \json_encode(['other_id' => 3])],
        ], $actual);
    }
}
