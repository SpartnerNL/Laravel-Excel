<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class ToArrayTest extends TestCase
{
    /**
     * @test
     */
    public function can_import_to_array()
    {
        $import = new class implements ToArray
        {
            use Importable;

            public $called = false;

            /**
             * @param array $array
             */
            public function array(array $array)
            {
                $this->called = true;

                Assert::assertEquals([
                    [
                        ['test', 'test'],
                        ['test', 'test']
                    ]
                ], $array);
            }
        };

        $import->import('import.xlsx');

        $this->assertTrue($import->called);
    }
}
