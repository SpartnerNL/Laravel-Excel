<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithReadFilter;
use Maatwebsite\Excel\Tests\Data\Stubs\FirstColumnReadFilter;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedImportWithReadFilter;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PHPUnit\Framework\Assert;

final class WithReadFilterTest extends TestCase
{
    public static bool $spy = false;

    public function test_can_register_custom_read_filter(): void
    {
        WithReadFilterTest::$spy = false;
        $export                  = new class implements Import, WithReadFilter
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

    public function test_applies_column_read_filter_when_importing_in_chunks(): void
    {
        $filter = new FirstColumnReadFilter;

        $this->assertSame([['Patrick Brouwers'], ['Taylor Otwell']], $this->importInChunks($filter));
        $this->assertSame($this->importWithoutChunks($filter), $this->importInChunks($filter));
    }

    public function test_applies_row_read_filter_when_importing_in_chunks(): void
    {
        $filter = new class implements IReadFilter
        {
            public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
            {
                return $row === 1;
            }
        };

        $this->assertSame([['Patrick Brouwers', 'patrick@maatwebsite.nl']], $this->importInChunks($filter));
        $this->assertSame($this->importWithoutChunks($filter), $this->importInChunks($filter));
    }

    public function test_applies_read_filter_when_queueing_an_import_in_chunks(): void
    {
        QueuedImportWithReadFilter::$rows = [];

        (new QueuedImportWithReadFilter)->queue('import-users.xlsx');

        $this->assertSame([['Patrick Brouwers'], ['Taylor Otwell']], QueuedImportWithReadFilter::$rows);
    }

    /**
     * @return array<int, array<array-key, mixed>>
     */
    private function importInChunks(IReadFilter $filter): array
    {
        $import = new class($filter) implements ToArray, WithChunkReading, WithReadFilter
        {
            use Importable;

            /**
             * @var array<int, array<array-key, mixed>>
             */
            public array $rows = [];

            public function __construct(private readonly IReadFilter $filter)
            {
            }

            public function array(array $array): void
            {
                $this->rows = [...$this->rows, ...$array];
            }

            public function chunkSize(): int
            {
                return 1;
            }

            public function readFilter(): IReadFilter
            {
                return $this->filter;
            }
        };

        $import->import('import-users.xlsx');

        return $import->rows;
    }

    /**
     * @return array<int, array<array-key, mixed>>
     */
    private function importWithoutChunks(IReadFilter $filter): array
    {
        $import = new class($filter) implements Import, WithReadFilter
        {
            use Importable;

            public function __construct(private readonly IReadFilter $filter)
            {
            }

            public function readFilter(): IReadFilter
            {
                return $this->filter;
            }
        };

        return $import->toArray('import-users.xlsx')[0];
    }
}
