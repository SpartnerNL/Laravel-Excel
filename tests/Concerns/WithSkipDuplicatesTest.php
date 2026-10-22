<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
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
        $this->loadMigrationsFrom(__DIR__ . '/../Data/Stubs/Database/Migrations');
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

    public function test_sets_timestamps_when_skipping_duplicate_models_in_rows(): void
    {
        Carbon::setTestNow('2026-01-01 10:00:00');

        User::create([
            'name'     => 'Funny Potato',
            'email'    => 'patrick@maatwebsite.nl',
            'password' => 'password',
        ]);

        Carbon::setTestNow('2026-01-02 10:00:00');

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
        };

        $import->import('import-users.xlsx');

        $this->assertDatabaseHas('users', [
            'email'      => 'patrick@maatwebsite.nl',
            'name'       => 'Funny Potato',
            'created_at' => '2026-01-01 10:00:00',
            'updated_at' => '2026-01-01 10:00:00',
        ]);

        $this->assertDatabaseHas('users', [
            'email'      => 'taylor@laravel.com',
            'created_at' => '2026-01-02 10:00:00',
            'updated_at' => '2026-01-02 10:00:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_keeps_given_timestamps_when_skipping_duplicate_models_in_rows(): void
    {
        Carbon::setTestNow('2026-01-02 10:00:00');

        $import = new class implements ToModel, WithSkipDuplicates
        {
            use Importable;

            public function model(array $row): User
            {
                return new User([
                    'name'       => $row[0],
                    'email'      => $row[1],
                    'password'   => 'secret',
                    'created_at' => '2020-05-01 08:00:00',
                ]);
            }
        };

        $import->import('import-users.xlsx');

        $this->assertDatabaseHas('users', [
            'email'      => 'taylor@laravel.com',
            'created_at' => '2020-05-01 08:00:00',
            'updated_at' => '2026-01-02 10:00:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_leaves_timestamps_empty_for_models_without_timestamps_when_skipping_duplicates_in_rows(): void
    {
        $import = new class implements ToModel, WithSkipDuplicates
        {
            use Importable;

            public function model(array $row): Group
            {
                return new Group([
                    'name' => $row[0],
                ]);
            }
        };

        $import->import('import-users.xlsx');

        $this->assertSame(2, Group::query()->whereNull('created_at')->whereNull('updated_at')->count());
    }
}
