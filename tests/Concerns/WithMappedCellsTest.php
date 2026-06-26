<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithMappedCellsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_can_import_with_references_to_cells(): void
    {
        $import = new class implements ToArray, WithMappedCells
        {
            use Importable;

            public function mapping(): array
            {
                return [
                    'name'  => 'B1',
                    'email' => 'B2',
                ];
            }

            public function array(array $array): void
            {
                Assert::assertSame([
                    'name'  => 'Patrick Brouwers',
                    'email' => 'patrick@maatwebsite.nl',
                ], $array);
            }
        };

        $import->import('mapped-import.xlsx');
    }

    public function test_can_import_with_nested_references_to_cells(): void
    {
        $import = new class implements ToArray, WithMappedCells
        {
            use Importable;

            public function mapping(): array
            {
                return [
                    [
                        'name'  => 'B1',
                        'email' => 'B2',
                    ],
                    [
                        'name'  => 'D1',
                        'email' => 'D2',
                    ],
                ];
            }

            public function array(array $array): void
            {
                Assert::assertSame([
                    [
                        'name'  => 'Patrick Brouwers',
                        'email' => 'patrick@maatwebsite.nl',
                    ],
                    [
                        'name'  => 'Typingbeaver',
                        'email' => 'typingbeaver@mailbox.org',
                    ],
                ], $array);
            }
        };

        $import->import('mapped-import.xlsx');
    }

    public function test_can_import_with_references_to_cells_to_model(): void
    {
        $import = new class implements ToModel, WithMappedCells
        {
            use Importable;

            public function mapping(): array
            {
                return [
                    'name'  => 'B1',
                    'email' => 'B2',
                ];
            }

            public function model(array $array): User
            {
                Assert::assertSame([
                    'name'  => 'Patrick Brouwers',
                    'email' => 'patrick@maatwebsite.nl',
                ], $array);

                $array['password'] = Str::random();

                return new User($array);
            }
        };

        $import->import('mapped-import.xlsx');

        $this->assertDatabaseHas('users', [
            'name'  => 'Patrick Brouwers',
            'email' => 'patrick@maatwebsite.nl',
        ]);
    }
}
