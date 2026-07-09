<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\TestCase;

class RemembersChunkOffsetTest extends TestCase
{
    public function test_can_set_and_get_chunk_offset(): void
    {
        $import = new class
        {
            use Importable;
            use RemembersChunkOffset;
        };

        $import->setChunkOffset(50);

        $this->assertSame(50, $import->getChunkOffset());
    }

    public function test_can_access_chunk_offset_on_import_to_array_in_chunks(): void
    {
        $import = new class implements ToArray, WithChunkReading
        {
            use Importable;
            use RemembersChunkOffset;

            /** @var array<int, int> */
            public array $offsets = [];

            public function array(array $array): void
            {
                $this->offsets[] = $this->getChunkOffset();
            }

            public function chunkSize(): int
            {
                return 2000;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertSame([1, 2001, 4001], $import->offsets);
    }
}
