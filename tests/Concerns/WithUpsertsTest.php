<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

final class WithUpsertsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        if (!method_exists(Builder::class, 'upsert')) {
            $this->markTestSkipped('The upsert feature is available on Laravel 8.10+');
        }

        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_can_upsert_models_in_batches(): void
    {
        User::create([
            'name'     => 'Funny Banana',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithBatchInserts, WithUpserts
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

            public function uniqueBy(): string
            {
                return 'email';
            }

            public function batchSize(): int
            {
                return 2;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(1, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $this->assertDatabaseHas('users', [
            'name'     => 'Patrick Brouwers',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'secret',
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => 'Taylor Otwell',
            'email'    => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $this->assertEquals(2, User::count());
    }

    public function test_can_upsert_models_in_rows(): void
    {
        User::create([
            'name'     => 'Funny Potato',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithUpserts
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

            public function uniqueBy(): string
            {
                return 'email';
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $this->assertDatabaseHas('users', [
            'name'     => 'Patrick Brouwers',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'secret',
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => 'Taylor Otwell',
            'email'    => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $this->assertEquals(2, User::count());
    }

    public function test_can_upsert_models_in_batches_with_defined_upsert_columns(): void
    {
        User::create([
            'name'     => 'Funny Banana',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithBatchInserts, WithUpsertColumns, WithUpserts
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

            public function uniqueBy(): string
            {
                return 'email';
            }

            public function upsertColumns(): array
            {
                return ['name'];
            }

            public function batchSize(): int
            {
                return 2;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(1, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $this->assertDatabaseHas('users', [
            'name'     => 'Patrick Brouwers',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => 'Taylor Otwell',
            'email'    => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $this->assertEquals(2, User::count());
    }

    public function test_can_upsert_models_in_rows_with_defined_upsert_columns(): void
    {
        User::create([
            'name'     => 'Funny Potato',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithUpsertColumns, WithUpserts
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

            public function uniqueBy(): string
            {
                return 'email';
            }

            public function upsertColumns(): array
            {
                return ['name'];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $this->assertDatabaseHas('users', [
            'name'     => 'Patrick Brouwers',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => 'Taylor Otwell',
            'email'    => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $this->assertEquals(2, User::count());
    }
}
