<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\ReadChunk;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

class ReadChunkDefaultsTest extends TestCase
{
    public function test_readchunk_falls_back_to_tries_one_when_import_has_no_retry_config()
    {
        $readChunk = $this->makeReadChunk($this->importWithoutRetryConfig());

        $this->assertSame(1, $readChunk->tries);
    }

    public function test_readchunk_falls_back_to_exponential_backoff_when_import_has_no_retry_config()
    {
        $readChunk = $this->makeReadChunk($this->importWithoutRetryConfig());

        $this->assertSame([30, 60, 300], $readChunk->backoff);
    }

    public function test_readchunk_preserves_user_tries_set_on_import()
    {
        $readChunk = $this->makeReadChunk($this->importWithTriesSetTo(7));

        $this->assertSame(7, $readChunk->tries);
    }

    /**
     * @return WithChunkReading
     */
    private function importWithoutRetryConfig(): WithChunkReading
    {
        return new class implements WithChunkReading
        {
            public function chunkSize(): int
            {
                return 100;
            }
        };
    }

    /**
     * @return WithChunkReading
     */
    private function importWithTriesSetTo(int $tries): WithChunkReading
    {
        return new class($tries) implements WithChunkReading
        {
            public $tries;

            public function __construct(int $tries)
            {
                $this->tries = $tries;
            }

            public function chunkSize(): int
            {
                return 100;
            }
        };
    }

    /**
     * @return ReadChunk
     */
    private function makeReadChunk(WithChunkReading $import): ReadChunk
    {
        return new ReadChunk(
            $import,
            $this->createMock(IReader::class),
            $this->createMock(TemporaryFile::class),
            'Sheet1',
            $import,
            1,
            100
        );
    }
}
