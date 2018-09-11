<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Reader;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PHPUnit\Framework\Assert;

class ToCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function can_import_to_collection()
    {
        $import = new class implements ToCollection
        {
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
                        ['test', 'test']
                    ]
                ], $collection->toArray());
            }
        };

        $import->import('import.xlsx');

        $this->assertTrue($import->called);
    }
}
