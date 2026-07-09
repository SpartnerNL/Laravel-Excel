<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Faker\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\PersistRelations;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

final class ToModelTest extends TestCase
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

    public function test_can_import_each_row_to_model(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel
        {
            use Importable;

            public function model(array $row): User
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

    public function test_has_timestamps_when_imported_single_model(): void
    {
        $import = new class implements ToModel
        {
            use Importable;

            public function model(array $row): User
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

    public function test_can_import_multiple_models_in_single_to_model(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel
        {
            use Importable;

            /**
             * @return array{0: User, 1: User}
             */
            public function model(array $row): array
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

    public function test_can_import_multiple_different_types_of_models_in_single_to_model(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel
        {
            use Importable;

            /**
             * @return array{0: User, 1: Group}
             */
            public function model(array $row): array
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
        $this->assertSame(2, User::count());
        $this->assertSame(2, Group::count());
        DB::connection()->disableQueryLog();
    }

    public function test_can_import_models_with_belongs_to_relations(): void
    {
        User::query()->truncate();
        Group::query()->truncate();

        DB::connection()->enableQueryLog();

        $import = new class implements PersistRelations, ToModel
        {
            use Importable;

            public function model(array $row): User
            {
                $user = new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);

                $user->group()->associate(
                    new Group([
                        'name' => $row[0],
                    ])
                );

                return $user;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(6, DB::getQueryLog());

        $users = User::all();
        $users->each(function (User $user): void {
            $this->assertInstanceOf(Group::class, $user->group);
        });

        $this->assertCount(2, $users);
        $this->assertSame(2, Group::count());
        DB::connection()->disableQueryLog();
    }

    public function test_can_import_models_with_belongs_to_many_relations(): void
    {
        User::query()->truncate();
        Group::query()->truncate();

        DB::connection()->enableQueryLog();

        $import = new class implements PersistRelations, ToModel
        {
            use Importable;

            public function model(array $row): User
            {
                $user = new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);

                $user->setRelation('groups', new Collection([
                    new Group([
                        'name' => $row[0],
                    ]),
                ]));

                return $user;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(6, DB::getQueryLog());

        $users = User::all();
        $users->each(function (User $user): void {
            $this->assertInstanceOf(Group::class, $user->groups->first());
        });

        $this->assertCount(2, $users);
        $this->assertSame(2, Group::count());
        DB::connection()->disableQueryLog();
    }
}
