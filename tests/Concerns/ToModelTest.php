<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Concerns\Importable;

class ToModelTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    /**
     * @test
     */
    public function can_import_each_row_to_model()
    {
        $import = new class implements ToModel
        {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model
             */
            public function model(array $row): Model
            {
                return new User([
                    'name'  => $row[0],
                    'email' => $row[1],
                ]);
            }
        };

        $import->import('import-users.xlsx');

        $this->assertDatabaseHas('users', [
            'name' => 'Patrick Brouwers',
            'email' => 'patrick@maatwebsite.nl',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
        ]);
    }
}
