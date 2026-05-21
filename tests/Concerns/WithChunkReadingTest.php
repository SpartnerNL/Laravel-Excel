<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithFormatData;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PHPUnit\Framework\Assert;
use Throwable;

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

    public function test_can_import_to_model_in_chunks_un(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithEvents
        {
            use Importable;

            public $before = 0;

            public $after = 0;

            public function model(array $row): ?Model
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            public function chunkSize(): int
            {
                return 1;
            }

            public function registerEvents(): array
            {
                return [
                    BeforeImport::class => function (BeforeImport $event): void {
                        Assert::assertInstanceOf(Reader::class, $event->reader);
                        $this->before++;
                    },
                    AfterImport::class => function (AfterImport $event): void {
                        Assert::assertInstanceOf(Reader::class, $event->reader);
                        $this->after++;
                    },
                ];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $this->assertEquals(1, $import->before, 'BeforeImport was not called or more than once.');
        $this->assertEquals(1, $import->after, 'AfterImport was not called or more than once.');
    }

    public function test_can_import_to_model_in_chunks_and_insert_in_batches(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithBatchInserts, WithChunkReading
        {
            use Importable;

            public function model(array $row): ?Model
            {
                return new Group([
                    'name' => $row[0],
                ]);
            }

            public function chunkSize(): int
            {
                return 1000;
            }

            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertCount(5000 / $import->batchSize(), DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    public function test_can_import_to_model_in_chunks_and_insert_in_batches_with_heading_row(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow
        {
            use Importable;

            public function model(array $row): ?Model
            {
                return new Group([
                    'name' => $row['name'],
                ]);
            }

            public function chunkSize(): int
            {
                return 1000;
            }

            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches-with-heading-row.xlsx');

        $this->assertCount(5000 / $import->batchSize(), DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    public function test_can_import_csv_in_chunks_and_insert_in_batches(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithBatchInserts, WithChunkReading
        {
            use Importable;

            public function model(array $row): ?Model
            {
                return new Group([
                    'name' => $row[0],
                ]);
            }

            public function chunkSize(): int
            {
                return 1000;
            }

            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches.csv');

        $this->assertCount(10, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    public function test_can_import_to_model_in_chunks_and_insert_in_batches_with_multiple_sheets(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithBatchInserts, WithChunkReading
        {
            use Importable;

            public function model(array $row): ?Model
            {
                return new Group([
                    'name' => $row[0],
                ]);
            }

            public function chunkSize(): int
            {
                return 1000;
            }

            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches-multiple-sheets.xlsx');

        $this->assertCount(10, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    public function test_can_import_to_array_in_chunks(): void
    {
        $import = new class implements ToArray, WithChunkReading, WithFormatData
        {
            use Importable;

            public $called = 0;

            public function array(array $array): void
            {
                $this->called++;

                Assert::assertCount(100, $array);
            }

            public function chunkSize(): int
            {
                return 100;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertEquals(50, $import->called);
    }

    public function test_can_import_to_model_in_chunks_and_insert_in_batches_with_multiple_sheets_objects_by_index(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements WithChunkReading, WithMultipleSheets
        {
            use Importable;

            public function chunkSize(): int
            {
                return 1000;
            }

            public function sheets(): array
            {
                return [
                    new class implements ToModel, WithBatchInserts
                    {
                        public function model(array $row): ?Model
                        {
                            return new Group([
                                'name' => $row[0],
                            ]);
                        }

                        public function batchSize(): int
                        {
                            return 1000;
                        }
                    },

                    new class implements ToModel, WithBatchInserts
                    {
                        public function model(array $row): ?Model
                        {
                            return new Group([
                                'name' => $row[0],
                            ]);
                        }

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

    public function test_can_import_to_model_in_chunks_and_insert_in_batches_with_multiple_sheets_objects_by_name(): void
    {
        DB::connection()->enableQueryLog();

        $import = new class implements WithChunkReading, WithMultipleSheets
        {
            use Importable;

            public function chunkSize(): int
            {
                return 1000;
            }

            public function sheets(): array
            {
                return [
                    'Worksheet' => new class implements ToModel, WithBatchInserts
                    {
                        public function model(array $row): ?Model
                        {
                            return new Group([
                                'name' => $row[0],
                            ]);
                        }

                        public function batchSize(): int
                        {
                            return 1000;
                        }
                    },

                    'Worksheet2' => new class implements ToModel, WithBatchInserts
                    {
                        public function model(array $row): ?Model
                        {
                            return new Group([
                                'name' => $row[0],
                            ]);
                        }

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

    public function test_can_catch_job_failed_in_chunks(): void
    {
        $import = new class implements ToModel, WithChunkReading, WithEvents
        {
            use Importable;

            public $failed = false;

            public function model(array $row): ?Model
            {
                throw new Exception('Something went wrong in the chunk');
            }

            public function chunkSize(): int
            {
                return 1;
            }

            public function registerEvents(): array
            {
                return [
                    ImportFailed::class => function (ImportFailed $event): void {
                        Assert::assertInstanceOf(Throwable::class, $event->getException());
                        Assert::assertEquals('Something went wrong in the chunk', $event->e->getMessage());

                        $this->failed = true;
                    },
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (Throwable $e) {
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Something went wrong in the chunk', $e->getMessage());
        }

        $this->assertTrue($import->failed, 'ImportFailed event was not called.');
    }

    public function test_can_import_to_array_and_format_in_chunks(): void
    {
        config()->set('excel.imports.read_only', false);

        $import = new class implements ToArray, WithChunkReading, WithFormatData
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertCount(2, $array);
                Assert::assertCount(1, $array[0]);
                Assert::assertCount(1, $array[1]);
                Assert::assertIsString($array[0][0]);
                Assert::assertIsString($array[1][0]);
                Assert::assertEquals('01/12/22', $array[0][0]);
                Assert::assertEquals('2023-02-20', $array[1][0]);
            }

            public function chunkSize(): int
            {
                return 2;
            }
        };

        $import->import('import-batches-with-date.xlsx');
    }

    public function test_can_import_to_array_in_chunks_without_formatting(): void
    {
        config()->set('excel.imports.read_only', true);

        $import = new class implements ToArray, WithChunkReading
        {
            use Importable;

            public function array(array $array): void
            {
                Assert::assertCount(2, $array);
                Assert::assertCount(1, $array[0]);
                Assert::assertCount(1, $array[1]);
                Assert::assertIsInt($array[0][0]);
                Assert::assertIsInt($array[1][0]);
                Assert::assertEquals((int) Date::dateTimeToExcel(DateTime::createFromFormat('Y-m-d', '2022-12-01')->setTime(0, 0, 0, 0)), $array[0][0]);
                Assert::assertEquals((int) Date::dateTimeToExcel(DateTime::createFromFormat('Y-m-d', '2023-02-20')->setTime(0, 0, 0, 0)), $array[1][0]);
            }

            public function chunkSize(): int
            {
                return 2;
            }
        };

        $import->import('import-batches-with-date.xlsx');
    }
}
