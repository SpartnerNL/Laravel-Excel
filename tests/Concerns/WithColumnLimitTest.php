<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithColumnLimitTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    /**
     * @test
     */
    public function can_import_to_array_with_column_limit()
    {
        $import = new class implements ToArray, WithColumnLimit {
            use Importable;

            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    [
                        'Patrick Brouwers',
                    ],
                    [
                        'Taylor Otwell',
                    ],
                ], $array);
            }

            public function endColumn(): string
            {
                return 'A';
            }
        };

        $import->import('import-users.xlsx');
    }
}
