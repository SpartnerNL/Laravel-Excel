<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

final class WithCalculatedFormulasTest extends TestCase
{
    public function test_by_default_does_not_calculate_formulas(): void
    {
        $import = new class implements ToArray
        {
            use Importable;

            public bool $called = false;

            public function array(array $array): void
            {
                $this->called = true;

                Assert::assertSame('=1+1', $array[0][0]);
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_can_import_to_array_with_calculated_formulas(): void
    {
        $import = new class implements ToArray, WithCalculatedFormulas
        {
            use Importable;

            public bool $called = false;

            public function array(array $array): void
            {
                $this->called = true;

                Assert::assertSame(2, $array[0][0]);
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_can_import_to_model_with_calculated_formulas(): void
    {
        $import = new class implements ToModel, WithCalculatedFormulas
        {
            use Importable;

            public bool $called = false;

            public function model(array $row): ?Model
            {
                $this->called = true;

                Assert::assertSame(2, $row[0]);

                return null;
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }

    public function can_import_with_formulas_and_reference(): void
    {
        $import = new class implements ToModel, WithCalculatedFormulas, WithStartRow
        {
            use Importable;

            public bool $called = false;

            public function model(array $row): ?Model
            {
                $this->called = true;

                Assert::assertSame('julien', $row[1]);

                return null;
            }

            public function startRow(): int
            {
                return 2;
            }
        };

        $import->import('import-external-reference.xls');

        $this->assertTrue($import->called);
    }

    public function test_can_import_to_array_with_calculated_formulas_and_multi_sheet_references(): void
    {
        $import = new class implements HasReferencesToOtherSheets, Import, WithMultipleSheets
        {
            use Importable;

            public string $test = 'test1';

            /**
             * @return HasReferencesToOtherSheets[]
             */
            public function sheets(): array
            {
                return [
                    new class implements HasReferencesToOtherSheets, ToArray
                    {
                        public string $test = 'test2';

                        public function array(array $array): void
                        {
                            Assert::assertSame([
                                [1, 1],
                            ], $array);
                        }
                    },
                    new class implements HasReferencesToOtherSheets, ToArray, WithCalculatedFormulas
                    {
                        public string $test = 'test2';

                        public function array(array $array): void
                        {
                            Assert::assertSame([
                                [2],
                            ], $array);
                        }
                    },
                ];
            }
        };

        $import->import('import-formulas-multiple-sheets.xlsx');
    }

    public function test_can_import_to_array_with_calculated_formulas_and_skips_empty(): void
    {
        $import = new class implements SkipsEmptyRows, ToArray, WithCalculatedFormulas
        {
            use Importable;

            public bool $called = false;

            public function array(array $array): void
            {
                $this->called = true;

                Assert::assertSame(2, $array[0][0]);
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_can_import_to_model_with_calculated_formulas_and_skips_empty(): void
    {
        $import = new class implements SkipsEmptyRows, ToModel, WithCalculatedFormulas
        {
            use Importable;

            public bool $called = false;

            public function model(array $row): ?Model
            {
                $this->called = true;

                Assert::assertSame(2, $row[0]);

                return null;
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }
}
