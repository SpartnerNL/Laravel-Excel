<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Generator;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Tests\TestCase;

class FromGeneratorTest extends TestCase
{
    public function test_can_export_from_generator(): void
    {
        $export = new class implements FromGenerator
        {
            use Exportable;

            public function generator(): Generator
            {
                for ($i = 1; $i <= 2; $i++) {
                    yield ['test', 'test'];
                }
            }
        };

        $response = $export->store('from-generator-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-generator-store.xlsx', 'Xlsx');

        $this->assertSame(iterator_to_array($export->generator()), $contents);
    }
}
