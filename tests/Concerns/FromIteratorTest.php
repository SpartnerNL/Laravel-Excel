<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use ArrayIterator;
use Iterator;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromIterator;
use Maatwebsite\Excel\Tests\TestCase;

class FromIteratorTest extends TestCase
{
    public function test_can_export_from_iterator(): void
    {
        $export = new class implements FromIterator
        {
            use Exportable;

            /**
             * @return array<int, array<int, string>>
             */
            public function array(): array
            {
                return [
                    ['test', 'test'],
                    ['test', 'test'],
                ];
            }

            public function iterator(): Iterator
            {
                return new ArrayIterator($this->array());
            }
        };

        $response = $export->store('from-iterator-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-iterator-store.xlsx', 'Xlsx');

        $this->assertSame($export->array(), $contents);
    }
}
