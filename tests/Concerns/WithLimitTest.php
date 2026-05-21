<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithLimitTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_can_import_a_limited_section_of_rows_to_model_with_different_start_row(): void
    {
        $import = new class implements ToModel, WithLimit, WithStartRow
        {
            use Importable;

            public function model(array $row): Model
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            public function startRow(): int
            {
                return 5;
            }

            public function limit(): int
            {
                return 1;
            }
        };

        $import->import('import-users-with-different-heading-row.xlsx');

        $this->assertDatabaseHas('users', [
            'name'  => 'Patrick Brouwers',
            'email' => 'patrick@maatwebsite.nl',
        ]);

        $this->assertDatabaseMissing('users', [
            'name'  => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_import_to_array_with_limit(): void
    {
        $import = new class implements ToArray, WithLimit
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertEquals([
                    [
                        'Patrick Brouwers',
                        'patrick@maatwebsite.nl',
                    ],
                ], $array);
            }

            public function limit(): int
            {
                return 1;
            }
        };

        $import->import('import-users.xlsx');
    }

    public function test_can_import_single_with_heading_row(): void
    {
        $import = new class implements ToArray, WithHeadingRow, WithLimit
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertEquals([
                    [
                        'name'  => 'Patrick Brouwers',
                        'email' => 'patrick@maatwebsite.nl',
                    ],
                ], $array);
            }

            public function limit(): int
            {
                return 1;
            }
        };

        $import->import('import-users-with-headings.xlsx');
    }

    public function test_can_import_multiple_with_heading_row(): void
    {
        $import = new class implements ToArray, WithHeadingRow, WithLimit
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertEquals([
                    [
                        'name'  => 'Patrick Brouwers',
                        'email' => 'patrick@maatwebsite.nl',
                    ],
                    [
                        'name'  => 'Taylor Otwell',
                        'email' => 'taylor@laravel.com',
                    ],
                ], $array);
            }

            public function limit(): int
            {
                return 2;
            }
        };

        $import->import('import-users-with-headings.xlsx');
    }

    public function test_can_set_limit_bigger_than_row_size(): void
    {
        $import = new class implements ToArray, WithLimit
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertCount(2, $array);
            }

            public function limit(): int
            {
                return 10;
            }
        };

        $import->import('import-users.xlsx');
    }
}
