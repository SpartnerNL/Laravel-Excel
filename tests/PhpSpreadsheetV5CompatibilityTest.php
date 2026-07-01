<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithReadFilter;
use Maatwebsite\Excel\Exceptions\SheetNotFoundException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

final class PhpSpreadsheetV5CompatibilityTest extends TestCase
{
    // ---------------------------------------------------------------
    // IReadFilter typed signature tests (v5 changed param types)
    // ---------------------------------------------------------------

    public function test_read_filter_receives_typed_parameters(): void
    {
        $receivedTypes = [];

        $import = new class($receivedTypes) implements WithReadFilter
        {
            use Importable;

            private ?array $receivedTypes = null;

            public function __construct(&$receivedTypes)
            {
                $this->receivedTypes = &$receivedTypes;
            }

            public function readFilter(): IReadFilter
            {
                $receivedTypes = &$this->receivedTypes;

                return new class($receivedTypes) implements IReadFilter
                {
                    private ?array $receivedTypes = null;

                    public function __construct(&$receivedTypes)
                    {
                        $this->receivedTypes = &$receivedTypes;
                    }

                    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                    {
                        if ($this->receivedTypes === null || $this->receivedTypes === []) {
                            $this->receivedTypes = [
                                'columnAddress' => gettype($columnAddress),
                                'row'           => gettype($row),
                                'worksheetName' => gettype($worksheetName),
                            ];
                        }

                        return true;
                    }
                };
            }
        };

        $import->toArray('import.xlsx');

        $this->assertSame('string', $receivedTypes['columnAddress']);
        $this->assertSame('integer', $receivedTypes['row']);
        $this->assertSame('string', $receivedTypes['worksheetName']);
    }

    public function test_read_filter_receives_correct_column_and_row_values(): void
    {
        $capturedCells = [];

        $import = new class($capturedCells) implements WithReadFilter
        {
            use Importable;

            private ?array $capturedCells = null;

            public function __construct(&$capturedCells)
            {
                $this->capturedCells = &$capturedCells;
            }

            public function readFilter(): IReadFilter
            {
                $capturedCells = &$this->capturedCells;

                return new class($capturedCells) implements IReadFilter
                {
                    // @phpstan-ignore property.onlyWritten
                    private ?array $capturedCells = null;

                    public function __construct(&$capturedCells)
                    {
                        $this->capturedCells = &$capturedCells;
                    }

                    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                    {
                        $this->capturedCells[] = ['column' => $columnAddress, 'row' => $row];

                        return true;
                    }
                };
            }
        };

        $import->toArray('import.xlsx');

        $this->assertNotEmpty($capturedCells);

        $firstCell = $capturedCells[0];
        $this->assertMatchesRegularExpression('/^[A-Z]+$/', $firstCell['column']);
        $this->assertGreaterThanOrEqual(1, $firstCell['row']);
    }

    public function test_read_filter_can_filter_specific_rows(): void
    {
        $import = new class implements WithReadFilter
        {
            use Importable;

            public function readFilter(): IReadFilter
            {
                return new class implements IReadFilter
                {
                    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                    {
                        return $row === 1;
                    }
                };
            }
        };

        $result = $import->toArray('import-users.xlsx');

        // toArray returns array of sheets, each sheet is array of rows
        $this->assertCount(1, $result[0]);
    }

    public function test_read_filter_can_filter_specific_columns(): void
    {
        $import = new class implements WithReadFilter
        {
            use Importable;

            public function readFilter(): IReadFilter
            {
                return new class implements IReadFilter
                {
                    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                    {
                        return $columnAddress === 'A';
                    }
                };
            }
        };

        $result = $import->toArray('import.xlsx');

        foreach ($result[0] as $row) {
            $nonNullValues = array_filter($row, fn ($v): bool => $v !== null);
            $this->assertCount(1, $nonNullValues);
        }
    }

    // ---------------------------------------------------------------
    // WithCustomValueBinder typed signature tests (v5 added types)
    // ---------------------------------------------------------------

    public function test_custom_value_binder_is_not_applied_on_import(): void
    {
        $import = new class extends DefaultValueBinder implements WithCustomValueBinder
        {
            use Importable;

            public bool $bindValueCalled = false;

            public function bindValue(Cell $cell, mixed $value): bool
            {
                $this->bindValueCalled = true;

                return parent::bindValue($cell, $value);
            }
        };

        $result = $import->toArray('value-binder-import.xlsx');

        $this->assertFalse($import->bindValueCalled, 'PHPSpreadsheet v5 does not call value binders during import');
        $this->assertNotEmpty($result[0], 'Data should still be imported without the value binder');
    }

    public function test_import_returns_raw_values_without_value_binder_transformation(): void
    {
        // Since value binders are not called on import in v5,
        // numeric values like dates come through as raw Excel serial numbers.
        $import = new class
        {
            use Importable;
        };

        $result = $import->toArray('value-binder-import.xlsx');

        // Row 3 (index 2) should contain raw numeric Excel date serial numbers
        if (isset($result[0][2])) {
            foreach ($result[0][2] as $value) {
                if ($value !== null) {
                    $this->assertIsNumeric($value);
                }
            }
        }
    }

    public function test_default_value_binder_encodes_arrays_with_typed_signature(): void
    {
        $export = new class implements FromCollection
        {
            use Exportable;

            public function collection(): Collection
            {
                return collect([
                    [['nested' => 'value'], 'plain'],
                    [['a', 'b', 'c'], 'text'],
                ]);
            }
        };

        $export->store('value-binder-array-test.xlsx');

        $actual = $this->readAsArray(__DIR__ . '/Data/Disks/Local/value-binder-array-test.xlsx', 'Xlsx');

        $this->assertSame('{"nested":"value"}', $actual[0][0]);
        $this->assertSame('plain', $actual[0][1]);
        $this->assertSame('["a","b","c"]', $actual[1][0]);
        $this->assertSame('text', $actual[1][1]);
    }

    // ---------------------------------------------------------------
    // setCreateBlankSheetIfNoneRead tests (v5 behavior change)
    // ---------------------------------------------------------------

    public function test_unknown_sheet_name_is_skipped_with_skips_unknown_sheets(): void
    {
        $import = new class implements SkipsUnknownSheets, WithMultipleSheets
        {
            use Importable;

            public $skippedSheets = [];

            public function sheets(): array
            {
                return [
                    'Sheet1' => new class implements ToArray
                    {
                        public $data = [];

                        public function array(array $array): void
                        {
                            $this->data = $array;
                        }
                    },
                    'NonExistentSheet' => new class implements ToArray
                    {
                        public $data = [];

                        public function array(array $array): void
                        {
                            $this->data = $array;
                        }
                    },
                ];
            }

            public function onUnknownSheet($sheetName): void
            {
                $this->skippedSheets[] = $sheetName;
            }
        };

        $import->toArray('import-multiple-sheets.xlsx');

        $this->assertContains('NonExistentSheet', $import->skippedSheets);
    }

    public function test_mixed_valid_and_invalid_sheet_names_with_skips(): void
    {
        $import = new class implements SkipsUnknownSheets, WithMultipleSheets
        {
            use Importable;

            public $skippedSheets = [];

            public function sheets(): array
            {
                return [
                    'Sheet1' => new class
                    {
                    },
                    'NonExistent1' => new class
                    {
                    },
                    'Sheet2' => new class
                    {
                    },
                    'NonExistent2' => new class
                    {
                    },
                ];
            }

            public function onUnknownSheet($sheetName): void
            {
                $this->skippedSheets[] = $sheetName;
            }
        };

        $result = $import->toArray('import-multiple-sheets.xlsx');

        $this->assertContains('NonExistent1', $import->skippedSheets);
        $this->assertContains('NonExistent2', $import->skippedSheets);

        // Valid sheets should still have returned data
        $this->assertArrayHasKey('Sheet1', $result);
        $this->assertArrayHasKey('Sheet2', $result);
        $this->assertNotEmpty($result['Sheet1']);
        $this->assertNotEmpty($result['Sheet2']);
    }

    public function test_unknown_sheet_name_throws_exception_without_skips(): void
    {
        $this->expectException(SheetNotFoundException::class);

        $import = new class implements WithMultipleSheets
        {
            use Importable;

            public function sheets(): array
            {
                return [
                    'NonExistentSheet' => new class implements ToArray
                    {
                        public function array(array $array): void
                        {
                        }
                    },
                ];
            }
        };

        $import->toArray('import-multiple-sheets.xlsx');
    }
}
