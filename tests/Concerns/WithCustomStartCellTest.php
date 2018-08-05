<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class WithCustomStartCellTest extends TestCase
{
    /**
     * @var Excel
     */
    protected $SUT;

    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->app->make(Excel::class);
    }

    /**
     * @test
     */
    public function can_store_collection_with_custom_start_cell()
    {
        $export = new class implements FromCollection, WithCustomStartCell {
            /**
             * @return Collection
             */
            public function collection()
            {
                return collect([
                    ['A1', 'B1'],
                    ['A2', 'B2'],
                ]);
            }

            /**
             * @return string
             */
            public function startCell(): string
            {
                return 'B2';
            }
        };

        $this->SUT->store($export, 'custom-start-cell.csv');

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/custom-start-cell.csv', 'Csv');

        $this->assertEquals([
            [null, null, null],
            [null, 'A1', 'B1'],
            [null, 'A2', 'B2'],
        ], $contents);
    }
}
