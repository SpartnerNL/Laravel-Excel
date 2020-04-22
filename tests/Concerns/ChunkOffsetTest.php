<?php


namespace Maatwebsite\Excel\Tests\Concerns;


use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\ChunkOffset;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkOffset;
use Maatwebsite\Excel\Tests\TestCase;

class ChunkOffsetTest extends TestCase
{
    /**
     * @test
     */
    public function can_set_and_get_chunk_offset()
    {
        $import = new class implements WithChunkOffset {
            use Importable;
            use ChunkOffset;
        };

        $import->setChunkOffset(50);

        $this->assertEquals(50, $import->getChunkOffset());
    }

    /**
     * @test
     */
    public function requires_WithChunkOffset_interface()
    {
        $import = new class {
            use Importable;
            use ChunkOffset;
        };
        $import->setChunkOffset(50);

        $this->expectException(InvalidArgumentException::class);
        $import->getChunkOffset();
    }
}