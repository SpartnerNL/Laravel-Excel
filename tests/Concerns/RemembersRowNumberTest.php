<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\TestCase;

final class RemembersRowNumberTest extends TestCase
{
    public function test_can_set_and_get_row_number(): void
    {
        $import = new class implements Import
        {
            use Importable;
            use RemembersRowNumber;
        };

        $import->rememberRowNumber(50);

        $this->assertSame(50, $import->getRowNumber());
    }

    public function test_can_access_row_number_on_import_to_model(): void
    {
        $import = new class implements ToModel
        {
            use Importable;
            use RemembersRowNumber;

            /** @var array<int, int> */
            public array $rowNumbers = [];

            public function model(array $row): null
            {
                $this->rowNumbers[] = $this->getRowNumber();

                return null;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertSame([46, 47, 48, 49, 50, 51, 52, 53, 54, 55], array_slice($import->rowNumbers, 45, 10));
    }

    public function test_can_access_row_number_on_import_to_array_in_chunks(): void
    {
        $import = new class implements ToModel, WithChunkReading
        {
            use Importable;
            use RemembersRowNumber;

            /** @var array<int, int> */
            public array $rowNumbers = [];

            public function chunkSize(): int
            {
                return 50;
            }

            public function model(array $row): null
            {
                $this->rowNumbers[] = $this->getRowNumber();

                return null;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertSame([46, 47, 48, 49, 50, 51, 52, 53, 54, 55], array_slice($import->rowNumbers, 45, 10));
    }

    public function test_can_access_row_number_on_import_to_array_in_chunks_with_batch_inserts(): void
    {
        $import = new class implements ToModel, WithBatchInserts, WithChunkReading
        {
            use Importable;
            use RemembersRowNumber;

            /** @var array<int, int> */
            public array $rowNumbers = [];

            public function chunkSize(): int
            {
                return 50;
            }

            public function model(array $row): null
            {
                $this->rowNumbers[] = $this->rowNumber;

                return null;
            }

            public function batchSize(): int
            {
                return 50;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertSame([46, 47, 48, 49, 50, 51, 52, 53, 54, 55], array_slice($import->rowNumbers, 45, 10));
    }
}
