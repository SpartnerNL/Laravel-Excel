<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use PHPUnit\Framework\Assert;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;

class ToCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function can_import_to_collection()
    {
        $import = new class implements ToCollection {
            use Importable;

            public $called = false;

            /**
             * @param Collection $collection
             */
            public function collection(Collection $collection)
            {
                $this->called = true;

                Assert::assertEquals([
                    [
                        ['test', 'test'],
                        ['test', 'test'],
                    ],
                ], $collection->toArray());
            }
        };

        $import->import('import.xlsx');

        $this->assertTrue($import->called);
    }
}
