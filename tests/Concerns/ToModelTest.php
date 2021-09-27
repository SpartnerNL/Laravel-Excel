<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

class ToModelTest extends TestCase
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

    /**
     * @test
     */
    public function can_import_each_row_to_model()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|Model[]|null
             */
            public function model(array $row)
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();

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
    public function has_timestamps_when_imported_single_model()
    {
        $import = new class implements ToModel
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|Model[]|null
             */
            public function model(array $row)
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }
        };

        $import->import('import-users.xlsx');

        $user = User::first();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    /**
     * @test
     */
    public function can_import_multiple_models_in_single_to_model()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|Model[]|null
             */
            public function model(array $row)
            {
                $user1 = new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);

                $faker = Factory::create();

                $user2 = new User([
                    'name'     => $faker->name,
                    'email'    => $faker->email,
                    'password' => 'secret',
                ]);

                return [$user1, $user2];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(4, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_multiple_different_types_of_models_in_single_to_model()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|Model[]|null
             */
            public function model(array $row)
            {
                $user = new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);

                $group = new Group([
                    'name' => $row[0],
                ]);

                return [$user, $group];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(4, DB::getQueryLog());
        $this->assertEquals(2, User::count());
        $this->assertEquals(2, Group::count());
        DB::connection()->disableQueryLog();
    }
}
