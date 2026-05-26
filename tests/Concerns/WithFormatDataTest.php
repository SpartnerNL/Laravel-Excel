<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithFormatData;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

final class WithFormatDataTest extends TestCase
{
    public function test_by_default_import_to_array(): void
    {
        $import = new class implements ToArray
        {
            use Importable;

            public $called = false;

            public function array(array $array): void
            {
                $this->called = true;

                Assert::assertSame(44328, $array[0][0]);
            }
        };

        $import->import('import-format-data.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_can_import_to_array_with_format_data(): void
    {
        config()->set('excel.imports.read_only', false);
        $import = new class implements ToArray, WithFormatData
        {
            use Importable;

            public $called = false;

            public function array(array $array): void
            {
                $this->called = true;

                Assert::assertSame('5/12/2021', $array[0][0]);
            }
        };

        $import->import('import-format-data.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_can_import_to_array_with_format_data_and_skips_empty_rows(): void
    {
        config()->set('excel.imports.read_only', false);
        $import = new class implements SkipsEmptyRows, ToArray, WithFormatData
        {
            use Importable;

            public $called = false;

            public function array(array $array): void
            {
                $this->called = true;

                Assert::assertSame('5/12/2021', $array[0][0]);
            }
        };

        $import->import('import-format-data.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_by_default_import_to_collection(): void
    {
        $import = new class implements ToCollection
        {
            use Importable;

            public $called = false;

            public function collection(Collection $collection): ?Model
            {
                $this->called = true;

                Assert::assertSame(44328, $collection[0][0]);

                return null;
            }
        };

        $import->import('import-format-data.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_can_import_to_collection_with_format_data(): void
    {
        config()->set('excel.imports.read_only', false);
        $import = new class implements ToCollection, WithFormatData
        {
            use Importable;

            public $called = false;

            public function collection(Collection $collection): ?Model
            {
                $this->called = true;

                Assert::assertSame('5/12/2021', $collection[0][0]);

                return null;
            }
        };

        $import->import('import-format-data.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_by_default_import_to_model(): void
    {
        $import = new class implements ToModel
        {
            use Importable;

            public $called = false;

            public function model(array $row): ?Model
            {
                $this->called = true;

                Assert::assertSame(44328, $row[0]);

                return null;
            }
        };

        $import->import('import-format-data.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_can_import_to_model_with_format_data(): void
    {
        config()->set('excel.imports.read_only', false);
        $import = new class implements ToModel, WithFormatData
        {
            use Importable;

            public $called = false;

            public function model(array $row): ?Model
            {
                $this->called = true;

                Assert::assertSame('5/12/2021', $row[0]);

                return null;
            }
        };

        $import->import('import-format-data.xlsx');

        $this->assertTrue($import->called);
    }
}
