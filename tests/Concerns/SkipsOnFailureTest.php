<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
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

class SkipsOnFailureTest extends TestCase
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

            public $failures = 0;

            public function model(array $row): ?Model
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }

            public function onFailure(Failure ...$failures): void
            {
                $failure = $failures[0];

                Assert::assertEquals(2, $failure->row());
                Assert::assertEquals('1', $failure->attribute());
                Assert::assertEquals(['The selected 1 is invalid.'], $failure->errors());
                Assert::assertEquals(['Taylor Otwell', 'taylor@laravel.com'], $failure->values());
                Assert::assertEquals(2, $failure->jsonSerialize()['row']);
                Assert::assertEquals('1', $failure->jsonSerialize()['attribute']);
                Assert::assertEquals(['The selected 1 is invalid.'], $failure->jsonSerialize()['errors']);
                Assert::assertEquals(['Taylor Otwell', 'taylor@laravel.com'], $failure->jsonSerialize()['values']);

                $this->failures += \count($failures);
            }
        };

        $import->import('import-users.xlsx');

        $this->assertEquals(1, $import->failures);

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

            public $failures = 0;

            public function model(array $row): ?Model
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }

            public function onFailure(Failure ...$failures): void
            {
                $failure = $failures[0];

                Assert::assertEquals(2, $failure->row());
                Assert::assertEquals('1', $failure->attribute());
                Assert::assertEquals(['The selected 1 is invalid.'], $failure->errors());

                $this->failures += \count($failures);
            }

            public function batchSize(): int
            {
                return 100;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertEquals(1, $import->failures);

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

            public function model(array $row): ?Model
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

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

        $this->assertEquals(2, $failure->row());
        $this->assertEquals('1', $failure->attribute());
        $this->assertEquals(['The selected 1 is invalid.'], $failure->errors());

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
        $import = new class implements OnEachRow, SkipsOnFailure, WithValidation
        {
            use Importable, SkipsFailures;

            public function onRow(Row $row): ?Model
            {
                $row = $row->toArray();

                return User::create([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

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

            public function collection(Collection $rows)
            {
                return $rows->each(fn ($row) => User::create([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]));
            }

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
