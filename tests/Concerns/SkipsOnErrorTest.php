<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Validators\ValidationException;
use PHPUnit\Framework\Assert;
use Throwable;

class SkipsOnErrorTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_can_skip_on_error()
    {
        $import = new class implements ToModel, SkipsOnError
        {
            use Importable;

            public $errors = 0;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @param  Throwable  $e
             */
            public function onError(Throwable $e)
            {
                Assert::assertInstanceOf(QueryException::class, $e);
                Assert::stringContains($e->getMessage(), 'Duplicate entry \'patrick@maatwebsite.nl\'');

                $this->errors++;
            }
        };

        $import->import('import-users-with-duplicates.xlsx');

        $this->assertEquals(1, $import->errors);

        // Shouldn't have rollbacked other imported rows.
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_skip_errors_and_collect_all_errors_at_the_end()
    {
        $import = new class implements ToModel, SkipsOnError
        {
            use Importable, SkipsErrors;

            /**
             * @param  array  $row
             * @return Model|null
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

        $import->import('import-users-with-duplicates.xlsx');

        $this->assertCount(1, $import->errors());

        /** @var Throwable $e */
        $e = $import->errors()->first();

        $this->assertInstanceOf(QueryException::class, $e);
        $this->stringContains($e->getMessage(), 'Duplicate entry \'patrick@maatwebsite.nl\'');

        // Shouldn't have rollbacked other imported rows.
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_skip_on_error_when_using_oneachrow_with_validation()
    {
        $import = new class implements OnEachRow, WithValidation, SkipsOnError
        {
            use Importable;

            public $errors        = 0;
            public $processedRows = 0;

            /**
             * @param  Row  $row
             */
            public function onRow(Row $row)
            {
                $this->processedRows++;

                // This will be called for valid rows
                $rowArray = $row->toArray();

                User::create([
                    'name'     => $rowArray[0],
                    'email'    => $rowArray[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }

            /**
             * @param  Throwable  $e
             */
            public function onError(Throwable $e)
            {
                Assert::assertInstanceOf(ValidationException::class, $e);
                Assert::stringContains($e->getMessage(), 'The selected 1 is invalid');

                $this->errors++;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertEquals(1, $import->errors);
        $this->assertEquals(1, $import->processedRows); // Only the valid row should be processed

        // Should have inserted the valid row
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting the invalid row
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_skip_errors_and_collect_all_errors_when_using_oneachrow_with_validation()
    {
        $import = new class implements OnEachRow, WithValidation, SkipsOnError
        {
            use Importable, SkipsErrors;

            public $processedRows = 0;

            /**
             * @param  Row  $row
             */
            public function onRow(Row $row)
            {
                $this->processedRows++;

                // This will be called for valid rows
                $rowArray = $row->toArray();

                User::create([
                    'name'     => $rowArray[0],
                    'email'    => $rowArray[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(1, $import->errors());
        $this->assertEquals(1, $import->processedRows); // Only the valid row should be processed

        /** @var Throwable $e */
        $e = $import->errors()->first();

        $this->assertInstanceOf(ValidationException::class, $e);
        $this->stringContains($e->getMessage(), 'The selected 1 is invalid');

        // Should have inserted the valid row
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting the invalid row
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_skip_on_error_when_exception_thrown_in_onrow()
    {
        $import = new class implements OnEachRow, SkipsOnError
        {
            use Importable;

            public $errors        = 0;
            public $processedRows = 0;

            /**
             * @param  Row  $row
             */
            public function onRow(Row $row)
            {
                $this->processedRows++;

                $rowArray = $row->toArray();

                // Throw an exception for the second row (Taylor Otwell)
                if ($rowArray[1] === 'taylor@laravel.com') {
                    throw new \Exception('Custom error in onRow for Taylor');
                }

                User::create([
                    'name'     => $rowArray[0],
                    'email'    => $rowArray[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @param  Throwable  $e
             */
            public function onError(Throwable $e)
            {
                Assert::assertInstanceOf(\Exception::class, $e);
                Assert::assertEquals('Custom error in onRow for Taylor', $e->getMessage());

                $this->errors++;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertEquals(1, $import->errors);
        $this->assertEquals(2, $import->processedRows); // Both rows should be processed, but one throws exception

        // Should have inserted the valid row
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting the row that threw exception
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }

    public function test_can_skip_errors_and_collect_all_errors_when_exception_thrown_in_onrow()
    {
        $import = new class implements OnEachRow, SkipsOnError
        {
            use Importable, SkipsErrors;

            public $processedRows = 0;

            /**
             * @param  Row  $row
             */
            public function onRow(Row $row)
            {
                $this->processedRows++;

                $rowArray = $row->toArray();

                // Throw an exception for the second row (Taylor Otwell)
                if ($rowArray[1] === 'taylor@laravel.com') {
                    throw new \RuntimeException('Runtime error in onRow for Taylor');
                }

                User::create([
                    'name'     => $rowArray[0],
                    'email'    => $rowArray[1],
                    'password' => 'secret',
                ]);
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(1, $import->errors());
        $this->assertEquals(2, $import->processedRows); // Both rows should be processed, but one throws exception

        /** @var Throwable $e */
        $e = $import->errors()->first();

        $this->assertInstanceOf(\RuntimeException::class, $e);
        $this->assertEquals('Runtime error in onRow for Taylor', $e->getMessage());

        // Should have inserted the valid row
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        // Should have skipped inserting the row that threw exception
        $this->assertDatabaseMissing('users', [
            'email' => 'taylor@laravel.com',
        ]);
    }
}
