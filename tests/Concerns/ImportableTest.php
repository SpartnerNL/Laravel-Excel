<?php

namespace Seoperin\LaravelExcel\Tests\Concerns;

use PHPUnit\Framework\Assert;
use Seoperin\LaravelExcel\Importer;
use Seoperin\LaravelExcel\Tests\TestCase;
use Seoperin\LaravelExcel\Concerns\ToArray;
use Seoperin\LaravelExcel\Concerns\Importable;

class ImportableTest extends TestCase
{
    /**
     * @test
     */
    public function can_import_a_simple_xlsx_file()
    {
        $import = new class implements ToArray {
            use Importable;

            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $imported = $import->import('import.xlsx');

        $this->assertInstanceOf(Importer::class, $imported);
    }

    /**
     * @test
     */
    public function can_import_a_simple_xlsx_file_from_uploaded_file()
    {
        $import = new class implements ToArray {
            use Importable;

            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $import->import($this->givenUploadedFile(__DIR__ . '/../Data/Disks/Local/import.xlsx'));
    }
}
