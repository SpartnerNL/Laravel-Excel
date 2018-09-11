<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PHPUnit\Framework\Assert;

class OnEachRowTest extends TestCase
{
    /**
     * @test
     */
    public function can_import_each_row_individually()
    {
        $import = new class implements OnEachRow
        {
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

                ++$this->called;
            }
        };

        $import->import('import.xlsx');

        $this->assertEquals(2, $import->called);
    }
}
