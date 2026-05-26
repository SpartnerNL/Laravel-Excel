<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

final class WithHeadingRowTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_can_import_each_row_to_model_with_heading_row(): void
    {
        $import = new class implements ToModel, WithHeadingRow
        {
            use Importable;

            public function model(array $row): \Maatwebsite\Excel\Tests\Data\Stubs\Database\User
            {
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => 'secret',
                ]);
            }
        };

        $import->import('import-users-with-headings.xlsx');

        $this->assertDatabaseHas('users', [
            'name'  => 'Patrick Brouwers',
            'email' => 'patrick@maatwebsite.nl',
        ]);

        $this->assertDatabaseHas('users', [
            'name'  => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_import_each_row_to_model_with_different_heading_row(): void
    {
        $import = new class implements ToModel, WithHeadingRow
        {
            use Importable;

            public function model(array $row): \Maatwebsite\Excel\Tests\Data\Stubs\Database\User
            {
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => 'secret',
                ]);
            }

            public function headingRow(): int
            {
                return 4;
            }
        };

        $import->import('import-users-with-different-heading-row.xlsx');

        $this->assertDatabaseHas('users', [
            'name'  => 'Patrick Brouwers',
            'email' => 'patrick@maatwebsite.nl',
        ]);

        $this->assertDatabaseHas('users', [
            'name'  => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_import_to_array_with_heading_row(): void
    {
        $import = new class implements ToArray, WithHeadingRow
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
        };

        $import->import('import-users-with-headings.xlsx');
    }

    public function test_can_import_empty_rows_with_header(): void
    {
        $import = new class implements ToArray, WithHeadingRow
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertEmpty($array);
            }
        };

        $import->import('import-empty-users-with-headings.xlsx');
    }

    public function test_can_import_empty_models_with_header(): void
    {
        $import = new class implements ToModel, WithHeadingRow
        {
            use Importable;

            public function model(array $row): \Maatwebsite\Excel\Tests\Data\Stubs\Database\User
            {
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => 'secret',
                ]);
            }
        };

        $import->import('import-empty-users-with-headings.xlsx');
        $this->assertEmpty(User::all());
    }

    public function test_can_cast_empty_headers_to_indexed_int(): void
    {
        $import = new class implements ToCollection, WithHeadingRow
        {
            use Importable;

            public $called = false;

            public function collection(Collection $collection): void
            {
                $this->called = true;

                Assert::assertEquals([
                    0 => 0,
                    1 => 'email',
                    2 => 'status',
                    3 => 3,
                ], $collection->first()->keys()->toArray());
            }
        };

        $import->import('import-users-with-mixed-headings.xlsx');
        $this->assertTrue($import->called);
    }
}
