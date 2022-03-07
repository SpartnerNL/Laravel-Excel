<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithGroupedHeaders;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithGroupedHeadersTest extends TestCase
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
    public function can_import_to_array_with_grouped_headers()
    {
        $import = new class implements ToArray, WithHeadingRow, WithGroupedHeaders
        {
            use Importable;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    [
                        'name'    => 'Patrick Brouwers',
                        'email'   => 'patrick@maatwebsite.nl',
                        'options' => [
                            'laravel',
                            'excel'
                        ],
                    ],
                    [
                        'name'  => 'Taylor Otwell',
                        'email' => 'taylor@laravel.com',
                        'options' => [
                            'laravel',
                            'excel'
                        ],
                    ],
                ], $array);
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');
    }

    /**
     * @test
     */
    public function can_import_to_collection_with_grouped_headers()
    {
        $import = new class implements ToCollection, WithHeadingRow, WithGroupedHeaders
        {
            use Importable;

            public $called = false;

            /**
             * @param  Collection  $collection
             */
            public function collection(Collection $collection)
            {
                $this->called = true;

                Assert::assertEquals([
                    [
                        'name'    => 'Patrick Brouwers',
                        'email'   => 'patrick@maatwebsite.nl',
                        'options' => [
                            'laravel',
                            'excel'
                        ],
                    ],
                    [
                        'name'  => 'Taylor Otwell',
                        'email' => 'taylor@laravel.com',
                        'options' => [
                            'laravel',
                            'excel'
                        ],
                    ],
                ], $collection->toArray());
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');

        $this->assertTrue($import->called);
    }

}
