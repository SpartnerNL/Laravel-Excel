<?php

namespace Seoperin\LaravelExcel\Tests\Concerns;

use Seoperin\LaravelExcel\Row;
use PHPUnit\Framework\Assert;
use Seoperin\LaravelExcel\Tests\TestCase;
use Seoperin\LaravelExcel\Concerns\OnEachRow;
use Seoperin\LaravelExcel\Concerns\Importable;

class OnEachRowTest extends TestCase
{
    /**
     * @test
     */
    public function can_import_each_row_individually()
    {
        $import = new class implements OnEachRow {
            use Importable;

            public $called = 0;

            /**
             * @param Row $row
             */
            public function onRow(Row $row)
            {
                foreach ($row->getCellIterator() as $cell) {
                    Assert::assertEquals('test', $cell->getValue());
                }

                Assert::assertEquals([
                    'test', 'test',
                ], $row->toArray());

                $this->called++;
            }
        };

        $import->import('import.xlsx');

        $this->assertEquals(2, $import->called);
    }
}
