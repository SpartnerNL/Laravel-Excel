<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class OnEachRowTest extends TestCase
{
    public function test_can_import_each_row_individually()
    {
        $import = new class implements OnEachRow
        {
            use Importable;

            public $called = 0;

            /**
             * @param  Row  $row
             */
            public function onRow(Row $row)
            {
                foreach ($row->getCellIterator() as $cell) {
                    Assert::assertEquals('test', $cell->getValue());
                }

                Assert::assertEquals([
                    'test', 'test',
                ], $row->toArray());

                Assert::assertEquals('test', $row[0]);

                $this->called++;
            }
        };

        $import->import('import.xlsx');

        $this->assertEquals(2, $import->called);
    }

    public function test_it_respects_the_end_column()
    {
        $import = new class implements OnEachRow
        {
            use Importable;

            /**
             * @param  Row  $row
             */
            public function onRow(Row $row)
            {
                // Accessing a row as an array calls toArray() without an end
                // column. This saves the row in the cache, so we have to
                // invalidate the cache once the end column changes
                $row[0];

                Assert::assertEquals([
                    'test',
                ], $row->toArray(null, false, true, 'A'));
            }
        };

        $import->import('import.xlsx');
    }
}
