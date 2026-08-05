<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Validators\Failure;
use PHPUnit\Framework\Assert;

final class SkipsOnFailureTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_can_skip_on_error(): void
    {
        $import = new class implements SkipsOnFailure, ToModel, WithValidation
        {
            use Importable;

            public int $failures = 0;

            public function model(array $row): User
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return In[]
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }

            public function onFailure(Failure ...$failures): void
            {
                $failure = $failures[0];

                Assert::assertSame(2, $failure->row());
                Assert::assertSame('1', $failure->attribute());
                Assert::assertSame(['The selected 1 is invalid.'], $failure->errors());
                Assert::assertSame(['Taylor Otwell', 'taylor@laravel.com'], $failure->values());
                Assert::assertSame(2, $failure->jsonSerialize()['row']);
                Assert::assertSame('1', $failure->jsonSerialize()['attribute']);
                Assert::assertSame(['The selected 1 is invalid.'], $failure->jsonSerialize()['errors']);
                Assert::assertSame(['Taylor Otwell', 'taylor@laravel.com'], $failure->jsonSerialize()['values']);

                $this->failures += count($failures);
            }
        };

        $import->import('import-users.xlsx');

        $this->assertSame(1, $import->failures);

        // Shouldn't have rollbacked other imported rows.
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_skips_only_failed_rows_in_batch(): void
    {
        $import = new class implements SkipsOnFailure, ToModel, WithBatchInserts, WithValidation
        {
            use Importable;

            public int $failures = 0;

            public function model(array $row): User
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return In[]
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }

            public function onFailure(Failure ...$failures): void
            {
                $failure = $failures[0];

                Assert::assertSame(2, $failure->row());
                Assert::assertSame('1', $failure->attribute());
                Assert::assertSame(['The selected 1 is invalid.'], $failure->errors());

                $this->failures += count($failures);
            }

            public function batchSize(): int
            {
                return 100;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertSame(1, $import->failures);

        // Shouldn't have rollbacked/skipped the rest of the batch.
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_skip_failures_and_collect_all_failures_at_the_end(): void
    {
        $import = new class implements SkipsOnFailure, ToModel, WithValidation
        {
            use Importable, SkipsFailures;

            public function model(array $row): User
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return In[]
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(1, $import->failures());

        /** @var Failure $failure */
        $failure = $import->failures()->first();

        $this->assertSame(2, $failure->row());
        $this->assertSame('1', $failure->attribute());
        $this->assertSame(['The selected 1 is invalid.'], $failure->errors());

        // Shouldn't have rollbacked other imported rows.
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_validate_using_oneachrow_and_skipsonfailure(): void
    {
        $import = new class implements Import, OnEachRow, SkipsOnFailure, WithValidation
        {
            use Importable, SkipsFailures;

            public function onRow(Row $row): User
            {
                $row = $row->toArray();

                return User::create([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return In[]
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };
        $this->assertEmpty(User::all());

        $import->import('import-users.xlsx');

        $this->assertCount(1, $import->failures());

        // Shouldn't have rollbacked other imported rows.
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_validate_using_tocollection_and_skipsonfailure(): void
    {
        $import = new class implements SkipsOnFailure, ToCollection, WithValidation
        {
            use Importable, SkipsFailures;

            public function collection(Collection $collection): void
            {
                $collection->each(fn ($row) => User::create([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]));
            }

            /**
             * @return In[]
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };
        $this->assertEmpty(User::all());

        $import->import('import-users.xlsx');

        $this->assertCount(1, $import->failures());

        // Shouldn't have rollbacked other imported rows.
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }
}
