<?php

namespace Seoperin\LaravelExcel\Tests\Concerns;

use Seoperin\LaravelExcel\Excel;
use Illuminate\Support\Collection;
use Seoperin\LaravelExcel\Tests\TestCase;
use Seoperin\LaravelExcel\Concerns\FromCollection;
use Seoperin\LaravelExcel\Concerns\WithCustomStartCell;

class WithCustomStartCellTest extends TestCase
{
    /**
     * @var Excel
     */
    protected $SUT;

    protected function setUp(): void
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
