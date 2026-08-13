<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

final class WithColumnsImportBehaviourTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_on_each_row_receives_a_column_aware_row(): void
    {
        $import = new class implements OnEachRow, WithColumns
        {
            use Importable;

            /** @var array<int, array<string, mixed>> */
            public array $rows = [];

            /** @var array<int, mixed> */
            public array $offsets = [];

            public function onRow(Row $row): void
            {
                $this->rows[]    = $row->toArray();
                $this->offsets[] = $row['name'];
            }

            /**
             * @return array<string, Text>
             */
            public function columns(): array
            {
                return [
                    'B' => Text::make('Name', 'name'),
                    'C' => Text::make('Email', 'email'),
                ];
            }
        };

        $import->import('import-users-with-columns.xlsx');

        // Columns used to be ignored entirely on this path, so toArray() returned
        // the raw positional row and $row['name'] did not exist.
        $this->assertSame(['name' => 'Patrick Brouwers', 'email' => 'patrick@maatwebsite.nl'], $import->rows[0]);
        $this->assertSame('Patrick Brouwers', $import->offsets[0]);
    }

    public function test_skips_empty_rows_uses_the_declared_columns(): void
    {
        $import = new class implements SkipsEmptyRows, ToArray, WithColumns
        {
            use Importable;

            /** @var array<int, array<string, mixed>> */
            public array $rows = [];

            public function array(array $array): void
            {
                $this->rows = $array;
            }

            /**
             * @return Text[]
             */
            public function columns(): array
            {
                return [
                    'B' => Text::make('Name', 'name'),
                ];
            }
        };

        $import->import('import-users-with-columns.xlsx');

        $this->assertCount(2, $import->rows);
        $this->assertSame('Patrick Brouwers', $import->rows[0]['name']);
    }

    public function test_a_column_can_use_a_different_key_than_its_heading(): void
    {
        $import = new class implements ToArray, WithColumns, WithHeadingRow
        {
            use Importable;

            /** @var array<int, array<string, mixed>> */
            public array $rows = [];

            public function array(array $array): void
            {
                $this->rows = $array;
            }

            /**
             * @return Text[]
             */
            public function columns(): array
            {
                return [
                    // Heading text and array key are separate concepts: the title
                    // is what a reader sees, the attribute is what the code uses.
                    Text::make('Email Address', 'email'),
                    Text::make('Full Name', 'name')->key('user_name'),
                ];
            }
        };

        $import->import('import-users-with-columns-heading.xlsx');

        $this->assertArrayHasKey('email', $import->rows[0]);
        $this->assertArrayHasKey('user_name', $import->rows[0]);
        $this->assertSame('patrick@maatwebsite.nl', $import->rows[0]['email']);
        $this->assertSame('Patrick Brouwers', $import->rows[0]['user_name']);
    }

    public function test_can_import_to_model_with_a_heading_row(): void
    {
        $import = new class implements ToModel, WithColumns, WithHeadingRow
        {
            use Importable;

            public function model(array $row): User
            {
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return Text[]
             */
            public function columns(): array
            {
                return [
                    Text::make('Name', 'name'),
                    Text::make('Email', 'email'),
                ];
            }
        };

        $import->import('import-users-with-columns-heading.xlsx');

        $this->assertDatabaseHas('users', [
            'name'  => 'Patrick Brouwers',
            'email' => 'patrick@maatwebsite.nl',
        ]);
    }
}
