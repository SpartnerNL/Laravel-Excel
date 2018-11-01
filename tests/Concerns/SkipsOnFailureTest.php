<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use PHPUnit\Framework\Assert;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class SkipsOnFailureTest extends TestCase
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
    public function can_skip_on_error()
    {
        $import = new class implements ToModel, WithValidation, SkipsOnFailure {
            use Importable;

            public $failures = 0;

            /**
             * @param array $row
             *
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
             * @param Failure[] $failures
             */
            public function onFailure(Failure ...$failures)
            {
                $failure = $failures[0];

                Assert::assertEquals(2, $failure->row());
                Assert::assertEquals('1', $failure->attribute());
                Assert::assertEquals(['The selected 1 is invalid.'], $failure->errors());

                $this->failures += \count($failures);
            }
        };

        $import->import('import-users.xlsx');

        $this->assertEquals(1, $import->failures);

        // Shouldn't have rollbacked other imported rows.
        $this->assertDatabaseHas('users', [
            'email' => 'patrick@maatwebsite.nl',
        ]);
    }
}
