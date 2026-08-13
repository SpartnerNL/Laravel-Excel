<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Exceptions\ConcernConflictException;
use Maatwebsite\Excel\Exceptions\UnsupportedColumnException;
use Maatwebsite\Excel\Tests\TestCase;

final class WithColumnsConcernConflictTest extends TestCase
{
    public function test_columns_and_headings_conflict(): void
    {
        $export = new class implements FromArray, WithColumns, WithHeadings
        {
            use Exportable;

            public function array(): array
            {
                return [['name' => 'Patrick']];
            }

            public function headings(): array
            {
                return ['Name'];
            }

            /**
             * @return Text[]
             */
            public function columns(): array
            {
                return [Text::make('Name', 'name')];
            }
        };

        $this->expectException(ConcernConflictException::class);
        $this->expectExceptionMessage('Cannot use WithColumns and WithHeadings on the same sheet.');

        $export->store('columns-conflict.xlsx');
    }

    public function test_columns_and_mapping_conflict_on_export(): void
    {
        $export = new class implements FromArray, WithColumns, WithMapping
        {
            use Exportable;

            public function array(): array
            {
                return [['name' => 'Patrick']];
            }

            public function map($row): array
            {
                return [$row['name']];
            }

            /**
             * @return Text[]
             */
            public function columns(): array
            {
                return [Text::make('Name', 'name')];
            }
        };

        $this->expectException(ConcernConflictException::class);
        $this->expectExceptionMessage('Cannot use WithColumns and WithMapping on the same export.');

        $export->store('columns-conflict.xlsx');
    }

    public function test_columns_and_mapped_cells_conflict(): void
    {
        $import = new class implements ToArray, WithColumns, WithMappedCells
        {
            use Importable;

            public function array(array $array): void
            {
                //
            }

            public function mapping(): array
            {
                return ['name' => 'A2'];
            }

            /**
             * @return Text[]
             */
            public function columns(): array
            {
                return [Text::make('Name', 'name')];
            }
        };

        $this->expectException(ConcernConflictException::class);
        $this->expectExceptionMessage('Cannot use WithColumns and WithMappedCells on the same sheet.');

        $import->import('import-users.xlsx');
    }

    public function test_columns_and_column_limit_conflict(): void
    {
        $import = new class implements ToArray, WithColumnLimit, WithColumns
        {
            use Importable;

            public function array(array $array): void
            {
                //
            }

            public function endColumn(): string
            {
                return 'B';
            }

            /**
             * @return Text[]
             */
            public function columns(): array
            {
                return [Text::make('Name', 'name')];
            }
        };

        $this->expectException(ConcernConflictException::class);
        $this->expectExceptionMessage('Cannot use WithColumns and WithColumnLimit on the same sheet.');

        $import->import('import-users.xlsx');
    }

    public function test_columns_and_grouped_heading_row_conflict(): void
    {
        $import = new class implements ToArray, WithColumns, WithGroupedHeadingRow
        {
            use Importable;

            public function array(array $array): void
            {
                //
            }

            /**
             * @return Text[]
             */
            public function columns(): array
            {
                return [Text::make('Name', 'name')];
            }
        };

        $this->expectException(ConcernConflictException::class);
        $this->expectExceptionMessage('Cannot use WithColumns and WithGroupedHeadingRow on the same sheet.');

        $import->import('import-users-with-grouped-headers.xlsx');
    }

    public function test_multiple_columns_cannot_be_exported(): void
    {
        $export = new class implements FromArray, WithColumns
        {
            use Exportable;

            public function array(): array
            {
                return [['name' => 'Patrick']];
            }

            public function columns(): array
            {
                return [
                    [
                        Text::make('First', 'first'),
                        Text::make('Last', 'last'),
                    ],
                ];
            }
        };

        $this->expectException(UnsupportedColumnException::class);
        $this->expectExceptionMessage('Column::multiple() reads several values from a single cell');

        $export->store('columns-conflict.xlsx');
    }
}
