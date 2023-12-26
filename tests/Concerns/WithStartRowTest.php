<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithStartRowTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    /**
     * @test
     */
    public function can_import_each_row_to_model_with_different_start_row()
    {
        $import = new class implements ToModel, WithStartRow
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model
             */
            public function model(array $row): Model
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return int
             */
            public function startRow(): int
            {
                return 5;
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

    /**
     * @test
     */
    public function can_import_to_array_with_start_row()
    {
        $import = new class implements ToArray, WithStartRow
        {
            use Importable;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    [
                        'Patrick Brouwers',
                        'patrick@maatwebsite.nl',
                    ],
                    [
                        'Taylor Otwell',
                        'taylor@laravel.com',
                    ],
                ], $array);
            }

            /**
             * @return int
             */
            public function startRow(): int
            {
                return 5;
            }
        };

        $import->import('import-users-with-different-heading-row.xlsx');
    }
}
