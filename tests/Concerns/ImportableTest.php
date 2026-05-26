<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Importer;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

final class ImportableTest extends TestCase
{
    public function test_can_import_a_simple_xlsx_file(): void
    {
        $import = new class implements ToArray
        {
            use Importable;

            public function array(array $array): void
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

    public function test_can_import_a_simple_xlsx_file_from_uploaded_file(): void
    {
        $import = new class implements ToArray
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $import->import($this->givenUploadedFile(__DIR__ . '/../Data/Disks/Local/import.xlsx'));
    }

    public function test_can_import_a_simple_csv_file_with_html_tags_inside(): void
    {
        $import = new class implements ToArray
        {
            use Importable;

            public function array(array $array): void
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

    public function test_can_import_a_simple_xlsx_file_with_ignore_empty_set_to_true(): void
    {
        config()->set('excel.imports.ignore_empty', true);

        $import = new class implements ToArray
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $imported = $import->import('import-with-some-empty-rows.xlsx');

        $this->assertInstanceOf(Importer::class, $imported);
    }

    public function test_can_import_a_simple_xlsx_file_with_ignore_empty_set_to_false(): void
    {
        config()->set('excel.imports.ignore_empty', false);

        $import = new class implements ToArray
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                    ['', ''],
                    ['', ''],
                ], $array);
            }
        };

        $imported = $import->import('import-with-some-empty-rows.xlsx');

        $this->assertInstanceOf(Importer::class, $imported);
    }

    public function test_cannot_import_a_non_existing_xlsx_file(): void
    {
        $this->expectException(FileNotFoundException::class);

        $import = new class
        {
            use Importable;
        };

        $import->import('doesnotexistanywhere.xlsx');
    }
}
