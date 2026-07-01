<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

final class WithSkipDuplicatesTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_can_skip_duplicate_models_in_batches(): void
    {
        User::create([
            'name'     => 'Funny Banana',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithBatchInserts, WithSkipDuplicates
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
            'name'     => 'Funny Banana',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => 'Taylor Otwell',
            'email'    => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $this->assertSame(2, User::count());
    }

    public function test_can_skip_duplicate_models_in_rows(): void
    {
        User::create([
            'name'     => 'Funny Potato',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithSkipDuplicates
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
            'name'     => 'Funny Potato',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name'     => 'Taylor Otwell',
            'email'    => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $this->assertSame(2, User::count());
    }
}
