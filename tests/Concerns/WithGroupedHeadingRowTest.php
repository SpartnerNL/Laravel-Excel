<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithGroupedHeadingRowTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Data/Stubs/Database/Migrations');
    }

    public function test_can_import_to_array_with_grouped_headers(): void
    {
        $import = new class implements ToArray, WithGroupedHeadingRow
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertSame([
                    [
                        'name'    => 'Patrick Brouwers',
                        'email'   => 'patrick@maatwebsite.nl',
                        'options' => [
                            'laravel',
                            'excel',
                        ],
                    ],
                ], $array);
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');
    }

    public function test_can_import_oneachrow_with_grouped_headers(): void
    {
        $import = new class implements OnEachRow, WithGroupedHeadingRow
        {
            use Importable;

            public function onRow(Row $row): void
            {
                Assert::assertSame(
                    [
                        'name'    => 'Patrick Brouwers',
                        'email'   => 'patrick@maatwebsite.nl',
                        'options' => [
                            'laravel',
                            'excel',
                        ],
                    ],
                    $row->toArray()
                );
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');
    }

    public function test_can_import_to_collection_with_grouped_headers(): void
    {
        $import = new class implements ToCollection, WithGroupedHeadingRow
        {
            use Importable;

            public bool $called = false;

            public function collection(Collection $collection): void
            {
                $this->called = true;

                Assert::assertSame([
                    [
                        'name'    => 'Patrick Brouwers',
                        'email'   => 'patrick@maatwebsite.nl',
                        'options' => [
                            'laravel',
                            'excel',
                        ],
                    ],
                ], $collection->toArray());
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');

        $this->assertTrue($import->called);
    }

    public function test_can_import_each_row_to_model_with_grouped_headers(): void
    {
        $import = new class implements ToModel, WithGroupedHeadingRow
        {
            use Importable;

            public function model(array $row): User
            {
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => 'secret',
                    'options'  => $row['options'],
                ]);
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');

        $this->assertDatabaseHas('users', [
            'name'    => 'Patrick Brouwers',
            'email'   => 'patrick@maatwebsite.nl',
            'options' => '["laravel","excel"]',
        ]);
    }
}
