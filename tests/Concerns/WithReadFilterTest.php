<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithReadFilter;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PHPUnit\Framework\Assert;

class WithReadFilterTest extends TestCase
{
    public static bool $spy = false;

    public function test_can_register_custom_read_filter(): void
    {
        WithReadFilterTest::$spy = false;
        $export                  = new class implements WithReadFilter
        {
            use Importable;

            public function readFilter(): IReadFilter
            {
                return new class implements IReadFilter
                {
                    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                    {
                        WithReadFilterTest::$spy = true;

                        return true;
                    }
                };
            }
        };

        $export->toArray('import-users.xlsx');
        Assert::assertTrue(WithReadFilterTest::$spy);
    }
}
