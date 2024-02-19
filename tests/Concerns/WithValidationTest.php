<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Validators\ValidationException;
use PHPUnit\Framework\Assert;

class WithValidationTest extends TestCase
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

    public function test_can_validate_rows()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'The selected 1(field)? is invalid.',
            ]);

            $this->assertRegex(
                '/There was an error on row 2. The selected 1 (field)?is invalid./',
                $e->errors()[0][0]
            );
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_rows_with_closure_validation_rules()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => function ($attribute, $value, $onFail) {
                        if ($value !== 'patrick@maatwebsite.nl') {
                            $onFail(sprintf('Value in column 1 is not an allowed e-mail.'));
                        }
                    },
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'Value in column 1 is not an allowed e-mail.',
            ]);

            $this->assertRegex(
                '/There was an error on row 2. Value in column 1 is not an allowed e-mail./',
                $e->errors()[0][0]
            );
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_rows_with_custom_validation_rule_objects()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => new class implements \Illuminate\Contracts\Validation\Rule
                    {
                        /**
                         * @param  string  $attribute
                         * @param  mixed  $value
                         * @return bool
                         */
                        public function passes($attribute, $value)
                        {
                            return $value === 'patrick@maatwebsite.nl';
                        }

                        /**
                         * Get the validation error message.
                         *
                         * @return string|array
                         */
                        public function message()
                        {
                            return 'Value is not an allowed e-mail.';
                        }
                    },
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'Value is not an allowed e-mail.',
            ]);

            $this->assertRegex(
                '/There was an error on row 2. Value is not an allowed e-mail./',
                $e->errors()[0][0]
            );
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_rows_with_conditionality()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    'conditional_required_column' => 'required_if:1,patrick@maatwebsite.nl',
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 1, 'conditional_required_column', [
                'The conditional_required_column field is required when 1.1 is patrick@maatwebsite.nl.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_rows_with_unless_conditionality()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    'conditional_required_unless_column' => 'required_unless:1,patrick@maatwebsite.nl',
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, 'conditional_required_unless_column', [
                'The conditional_required_unless_column field is required unless 2.1 is in patrick@maatwebsite.nl.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_rows_with_combined_rules_with_colons()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => 'required_with:0|unique:users,email',
                ];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 1, '1', [
                'The 1 has already been taken.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_with_custom_attributes()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }

            /**
             * @return array
             */
            public function customValidationAttributes()
            {
                return ['1' => 'email'];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, 'email', [
                'The selected email is invalid.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_with_custom_attributes_pointing_to_another_attribute()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => ['required'],
                    '2' => ['required_with:*.1'],
                ];
            }

            /**
             * @return array
             */
            public function customValidationAttributes()
            {
                return ['1' => 'email', '2' => 'password'];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 1, 'password', [
                'The password field is required when email is present.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_with_custom_message()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }

            /**
             * @return array
             */
            public function customValidationMessages()
            {
                return [
                    '1.in' => 'Custom message for :attribute.',
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'Custom message for 1.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_rows_with_headings()
    {
        $import = new class implements ToModel, WithHeadingRow, WithValidation
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    'email' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        try {
            $import->import('import-users-with-headings.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 3, 'email', [
                'The selected email is invalid.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_rows_with_grouped_headings()
    {
        $import = new class implements ToModel, WithGroupedHeadingRow, WithValidation
        {
            use Importable;

            /**
             * Prepare the data for validation.
             *
             * @param  array  $row
             * @param  int  $index
             * @return array
             */
            public function prepareForValidation(array $row, int $index)
            {
                if ($index === 2) {
                    Assert::assertIsArray($row['options']);
                    $row['options'] = 'not an array';
                }

                return $row;
            }

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => 'secret',
                    'options'  => $row['options'],
                ]);
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    'options' => 'array',
                ];
            }
        };

        try {
            $import->import('import-users-with-grouped-headers.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, 'options', [
                'The options( field)? must be an array.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_rows_in_batches()
    {
        $import = new class implements ToModel, WithHeadingRow, WithBatchInserts, WithValidation
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 2;
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    'email' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        try {
            $import->import('import-users-with-headings.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 3, 'email', [
                'The selected email is invalid.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_using_oneachrow()
    {
        $import = new class implements OnEachRow, WithHeadingRow, WithValidation
        {
            use Importable;

            /**
             * @param  Row  $row
             * @return Model|null
             */
            public function onRow(Row $row)
            {
                $values = $row->toArray();

                return new User([
                    'name'     => $values['name'],
                    'email'    => $values['email'],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    'email' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        try {
            $import->import('import-users-with-headings.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 3, 'email', [
                'The selected email is invalid.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_using_collection()
    {
        $import = new class implements ToCollection, WithHeadingRow, WithValidation
        {
            use Importable;

            public function collection(Collection $rows)
            {
                //
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    'email' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        try {
            $import->import('import-users-with-headings.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 3, 'email', [
                'The selected email is invalid.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_validate_using_array()
    {
        $import = new class implements ToArray, WithHeadingRow, WithValidation
        {
            use Importable;

            public function array(array $rows)
            {
                //
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    'email' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        try {
            $import->import('import-users-with-headings.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 3, 'email', [
                'The selected email is invalid.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_configure_validator()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

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
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => 'email',
                ];
            }

            /**
             * Configure the validator.
             *
             * @param  \Illuminate\Contracts\Validation\Validator  $validator
             * @return void
             */
            public function withValidator($validator)
            {
                $validator->sometimes('*.1', Rule::in(['patrick@maatwebsite.nl']), function () {
                    return true;
                });
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'The selected 1 is invalid.',
            ]);

            $this->assertRegex(
                '/There was an error on row 2. The selected 1 (field)?is invalid./',
                $e->errors()[0][0]
            );
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_prepare_using_toarray()
    {
        $import = new class implements ToArray, WithValidation
        {
            use Importable;

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => 'email',
                ];
            }

            /**
             * Prepare the data for validation.
             *
             * @param  array  $row
             * @param  int  $index
             * @return array
             */
            public function prepareForValidation(array $row, int $index)
            {
                if ($index === 2) {
                    $row[1] = 'not an email';
                }

                return $row;
            }

            /**
             * @param  array  $array
             * @return array
             */
            public function array(array $array)
            {
                return [];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'The 1( field)? must be a valid email address.',
            ]);

            $this->assertRegex(
                '/There was an error on row 2. The 1( field)? must be a valid email address./',
                $e->errors()[0][0]
            );
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_prepare_using_tocollection()
    {
        $import = new class implements ToCollection, WithValidation
        {
            use Importable;

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => 'email',
                ];
            }

            /**
             * Prepare the data for validation.
             *
             * @param  array  $row
             * @param  int  $index
             * @return array
             */
            public function prepareForValidation(array $row, int $index)
            {
                if ($index === 2) {
                    $row[1] = 'not an email';
                }

                return $row;
            }

            /**
             * @param  \Illuminate\Support\Collection  $collection
             * @return mixed
             */
            public function collection(Collection $collection)
            {
                return collect();
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'The 1( field)? must be a valid email address.',
            ]);

            $this->assertRegex(
                '/There was an error on row 2. The 1( field)? must be a valid email address./',
                $e->errors()[0][0]
            );
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_prepare_using_tomodel()
    {
        $import = new class implements ToModel, WithValidation
        {
            use Importable;

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => 'email',
                ];
            }

            /**
             * Prepare the data for validation.
             *
             * @param  array  $row
             * @param  int  $index
             * @return array
             */
            public function prepareForValidation(array $row, int $index)
            {
                if ($index === 2) {
                    $row[1] = 'not an email';
                }

                return $row;
            }

            /**
             * @param  array  $row
             * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|null
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

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'The 1( field)? must be a valid email address.',
            ]);

            $this->assertRegex(
                '/There was an error on row 2. The 1( field)? must be a valid email address./',
                $e->errors()[0][0]
            );
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_prepare_using_oneachrow()
    {
        $import = new class implements OnEachRow, WithValidation
        {
            use Importable;

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => 'email',
                ];
            }

            /**
             * Prepare the data for validation.
             *
             * @param  array  $row
             * @param  int  $index
             * @return array
             */
            public function prepareForValidation(array $row, int $index)
            {
                if ($index === 2) {
                    $row[1] = 'not an email';
                }

                return $row;
            }

            /**
             * @param  \Maatwebsite\Excel\Row  $row
             * @return void
             */
            public function onRow(Row $row)
            {
                User::query()->create([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'The 1( field)? must be a valid email address.',
            ]);

            $this->assertRegex(
                '/There was an error on row 2. The 1( field)? must be a valid email address./',
                $e->errors()[0][0]
            );
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    public function test_can_prepare_using_skipsemptyrows()
    {
        $import = new class implements OnEachRow, WithValidation, SkipsEmptyRows
        {
            use Importable;

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => 'email',
                ];
            }

            /**
             * Prepare the data for validation.
             *
             * @param  array  $row
             * @param  int  $index
             * @return array
             */
            public function prepareForValidation(array $row, int $index)
            {
                if ($index === 2) {
                    $row[1] = 'not an email';
                }

                return $row;
            }

            /**
             * @param  \Maatwebsite\Excel\Row  $row
             * @return void
             */
            public function onRow(Row $row)
            {
                User::query()->create([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'The 1( field)? must be a valid email address.',
            ]);

            $this->assertRegex(
                '/There was an error on row 2. The 1( field)? must be a valid email address./',
                $e->errors()[0][0]
            );
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    /**
     * @param  ValidationException  $e
     * @param  int  $row
     * @param  string  $attribute
     * @param  array  $messages
     */
    private function validateFailure(ValidationException $e, int $row, string $attribute, array $messages)
    {
        $failures = $e->failures();
        $failure  = head($failures);

        $this->assertEquals($row, $failure->row());
        $this->assertEquals($attribute, $failure->attribute());
        $this->assertEquals($row, $failure->jsonSerialize()['row']);
        $this->assertEquals($attribute, $failure->jsonSerialize()['attribute']);

        $this->assertRegex('/' . $messages[0] . '/', $failure->errors()[0]);
        $this->assertRegex('/' . $messages[0] . '/', $failure->jsonSerialize()['errors'][0]);
    }
}
