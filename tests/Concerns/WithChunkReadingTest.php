<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use PHPUnit\Framework\Assert;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class WithChunkReadingTest extends TestCase
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

    /**
     * @test
     */
    public function can_import_to_model_in_chunks()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading {
            use Importable;

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
             * @return int
             */
            public function chunkSize(): int
            {
                return 1;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks_and_insert_in_batches()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithBatchInserts {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new Group([
                    'name'  => $row[0],
                ]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertCount(5000 / $import->batchSize(), DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks_and_insert_in_batches_with_heading_row()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithBatchInserts, WithHeadingRow {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new Group([
                    'name'  => $row['name'],
                ]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches-with-heading-row.xlsx');

        $this->assertCount(5000 / $import->batchSize(), DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_csv_in_chunks_and_insert_in_batches()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithBatchInserts {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new Group([
                    'name'  => $row[0],
                ]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches.csv');

        $this->assertCount(10, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks_and_insert_in_batches_with_multiple_sheets()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithBatchInserts {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new Group([
                    'name'  => $row[0],
                ]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches-multiple-sheets.xlsx');

        $this->assertCount(10, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_to_array_in_chunks()
    {
        $import = new class implements ToArray, WithChunkReading {
            use Importable;

            public $called = 0;

            /**
             * @param array $array
             */
            public function array(array $array)
            {
                $this->called++;

                Assert::assertCount(100, $array);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 100;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertEquals(50, $import->called);
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks_and_insert_in_batches_with_multiple_sheets_objects()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements WithMultipleSheets, WithChunkReading {
            use Importable;

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return array
             */
            public function sheets(): array
            {
                return [
                    new class implements ToModel, WithBatchInserts {
                        /**
                         * @param array $row
                         *
                         * @return Model|null
                         */
                        public function model(array $row)
                        {
                            return new Group([
                                'name'  => $row[0],
                            ]);
                        }

                        /**
                         * @return int
                         */
                        public function batchSize(): int
                        {
                            return 1000;
                        }
                    },

                    new class implements ToModel, WithBatchInserts {
                        /**
                         * @param array $row
                         *
                         * @return Model|null
                         */
                        public function model(array $row)
                        {
                            return new Group([
                                'name'  => $row[0],
                            ]);
                        }

                        /**
                         * @return int
                         */
                        public function batchSize(): int
                        {
                            return 2000;
                        }
                    },
                ];
            }
        };

        $import->import('import-batches-multiple-sheets.xlsx');

        $this->assertCount(10, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }
}
