<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\ChunkOffset;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkOffset;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\TestCase;

class WithChunkOffsetTest extends TestCase
{
    /**
     * @test
     */
    public function can_access_chunk_offset_on_import_to_array_in_chunks()
    {
        $import = new class implements ToArray, WithChunkReading, WithChunkOffset {
            use Importable;
            use ChunkOffset;

            public $offsets = [];

            public function array(array $array)
            {
                $this->offsets[] = $this->getChunkOffset();
            }

            public function chunkSize(): int
            {
                return 2000;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertEquals([1, 2001, 4001], $import->offsets);
    }
}
