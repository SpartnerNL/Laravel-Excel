<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Excel;
use PHPUnit\Framework\Assert;
use Maatwebsite\Excel\Importer;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\Importable;

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

    /**
     * @test
     */
    public function can_import_a_simple_csv_file_with_html_tags_inside()
    {
        $import = new class implements ToArray {
            use Importable;

            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['key1', 'A', 'row1'],
                    ['key2', 'B', '<p>row2</p>'],
                    ['key3', 'C', 'row3'],
                    ['key4', 'D', 'row4'],
                    ['key5', 'E', 'row5'],
                    ['key6', 'F', '<a href=/url-example">link</a>"'],
                ], $array);
            }
        };

        $import->import('csv-with-html-tags.csv', 'local', Excel::CSV);
    }
}
