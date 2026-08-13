<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Columns\Number;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Exceptions\ColumnCollisionException;
use Maatwebsite\Excel\Tests\TestCase;
use stdClass;

final class WithColumnsAlignmentTest extends TestCase
{
    public function test_headings_line_up_with_data_for_explicit_letters(): void
    {
        $export = new class implements FromArray, WithColumns
        {
            use Exportable;

            public function array(): array
            {
                return [['id' => 1, 'name' => 'Patrick', 'email' => 'patrick@maatwebsite.nl']];
            }

            /**
             * @return array<string, Text>
             */
            public function columns(): array
            {
                return [
                    'B' => Text::make('Name', 'name'),
                    'D' => Text::make('Email', 'email'),
                ];
            }
        };

        $export->store('columns-alignment.xlsx');

        $sheet = $this->read(__DIR__ . '/../Data/Disks/Local/columns-alignment.xlsx', 'Xlsx')->getActiveSheet();

        // The heading has to sit above its own data, not at the first free column.
        $this->assertNull($sheet->getCell('A1')->getValue());
        $this->assertSame('Name', $sheet->getCell('B1')->getValue());
        $this->assertSame('Email', $sheet->getCell('D1')->getValue());

        $this->assertSame('Patrick', $sheet->getCell('B2')->getValue());
        $this->assertSame('patrick@maatwebsite.nl', $sheet->getCell('D2')->getValue());
    }

    public function test_headings_line_up_with_data_when_declared_out_of_order(): void
    {
        $export = new class implements FromArray, WithColumns
        {
            use Exportable;

            public function array(): array
            {
                return [['id' => 1, 'name' => 'Patrick']];
            }

            /**
             * @return array<string, Column>
             */
            public function columns(): array
            {
                return [
                    'C' => Text::make('Name', 'name'),
                    'A' => Number::make('ID', 'id'),
                ];
            }
        };

        $export->store('columns-alignment.xlsx');

        $sheet = $this->read(__DIR__ . '/../Data/Disks/Local/columns-alignment.xlsx', 'Xlsx')->getActiveSheet();

        $this->assertSame('ID', $sheet->getCell('A1')->getValue());
        $this->assertSame('Name', $sheet->getCell('C1')->getValue());

        $this->assertSame(1, $sheet->getCell('A2')->getValue());
        $this->assertSame('Patrick', $sheet->getCell('C2')->getValue());
    }

    public function test_can_export_plain_objects(): void
    {
        $export = new class implements FromCollection, WithColumns
        {
            use Exportable;

            /**
             * A raw query builder hands back plain objects, not arrays.
             *
             * @return Collection<int, stdClass>
             */
            public function collection(): Collection
            {
                $row       = new stdClass;
                $row->id   = 1;
                $row->name = 'Patrick';

                return new Collection([$row]);
            }

            /**
             * @return Column[]
             */
            public function columns(): array
            {
                return [
                    Number::make('ID', 'id'),
                    Text::make('Name', 'name'),
                ];
            }
        };

        $export->store('columns-alignment.xlsx');

        $sheet = $this->read(__DIR__ . '/../Data/Disks/Local/columns-alignment.xlsx', 'Xlsx')->getActiveSheet();

        // Arr::get() cannot reach properties on a plain object, so these used to
        // come out empty for any export built from a raw query.
        $this->assertSame(1, $sheet->getCell('A2')->getValue());
        $this->assertSame('Patrick', $sheet->getCell('B2')->getValue());
    }

    public function test_positional_columns_follow_a_custom_start_cell(): void
    {
        $export = new class implements FromArray, WithColumns, WithCustomStartCell
        {
            use Exportable;

            public function array(): array
            {
                return [['id' => 1, 'name' => 'Patrick']];
            }

            public function startCell(): string
            {
                return 'C5';
            }

            /**
             * @return Column[]
             */
            public function columns(): array
            {
                return [
                    Number::make('ID', 'id'),
                    Text::make('Name', 'name'),
                ];
            }
        };

        $export->store('columns-alignment.xlsx');

        $sheet = $this->read(__DIR__ . '/../Data/Disks/Local/columns-alignment.xlsx', 'Xlsx')->getActiveSheet();

        $this->assertSame('ID', $sheet->getCell('C5')->getValue());
        $this->assertSame('Name', $sheet->getCell('D5')->getValue());

        // Data has to follow the start cell in both directions, not just down.
        $this->assertSame(1, $sheet->getCell('C6')->getValue());
        $this->assertSame('Patrick', $sheet->getCell('D6')->getValue());
    }

    public function test_colliding_columns_are_rejected(): void
    {
        // The explicit letter takes B, and the positional column that follows it
        // lands on B as well. Silently dropping one of them is how misaligned
        // exports used to happen.
        $export = new class implements FromArray, WithColumns
        {
            use Exportable;

            public function array(): array
            {
                return [['name' => 'Patrick', 'email' => 'patrick@maatwebsite.nl']];
            }

            /**
             * @return array<string|int, Text>
             */
            public function columns(): array
            {
                return [
                    'B' => Text::make('Email', 'email'),
                    Text::make('Name', 'name'),
                ];
            }
        };

        $this->expectException(ColumnCollisionException::class);
        $this->expectExceptionMessage('Multiple columns were placed on column B.');

        $export->store('columns-alignment.xlsx');
    }
}
