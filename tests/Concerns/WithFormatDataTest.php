<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithFormatData;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithFormatDataTest extends TestCase
{
    /**
     * @test
     */
    public function by_default_does_not_turn_on()
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

                Assert::assertSame(44328, $array[0][0]);
            }
        };

        $import->import('import-format-data.xlsx');

        $this->assertTrue($import->called);
    }

    /**
     * @test
     */
    public function can_import_to_array_with_format_data()
    {
        $import = new class implements ToArray, WithFormatData
        {
            use Importable;

            public $called = false;

            /**
             * @param array $array
             */
            public function array(array $array)
            {
                $this->called = true;

                Assert::assertSame('5/12/2021', $array[0][0]);
            }
        };

        $import->import('import-format-data.xlsx');

        $this->assertTrue($import->called);
    }
}
