<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

final class ToArrayTest extends TestCase
{
    public function test_can_import_to_array(): void
    {
        $import = new class implements ToArray
        {
            use Importable;

            public bool $called = false;

            public function array(array $array): void
            {
                $this->called = true;

                Assert::assertSame([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $import->import('import.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_can_import_multiple_sheets_to_array(): void
    {
        $import = new class implements ToArray
        {
            use Importable;

            public int $called = 0;

            public function array(array $array): void
            {
                $this->called++;

                $sheetNumber = $this->called;

                Assert::assertSame([
                    [$sheetNumber . '.A1', $sheetNumber . '.B1'],
                    [$sheetNumber . '.A2', $sheetNumber . '.B2'],
                ], $array);
            }
        };

        $import->import('import-multiple-sheets.xlsx');

        $this->assertSame(2, $import->called);
    }
}
