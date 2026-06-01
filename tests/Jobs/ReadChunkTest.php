<?php

namespace Maatwebsite\Excel\Tests\Jobs;

use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Files\LocalTemporaryFile;
use Maatwebsite\Excel\Jobs\ReadChunk;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedImportWithQueueAttribute;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ReadChunkTest extends TestCase
{
    public function test_resolves_the_queue_and_connection_from_attributes()
    {
        if (!class_exists(\Illuminate\Queue\Attributes\Queue::class)) {
            $this->markTestSkipped('The #[Queue] attribute is not available on this Laravel version');
        }

        $job = $this->readChunkFor(new QueuedImportWithQueueAttribute());

        $this->assertSame('excel-imports', $job->queue);
        $this->assertSame('redis', $job->connection);
    }

    public function test_keeps_a_string_queue_property()
    {
        $import = new class implements WithChunkReading {
            public $queue = 'plain-queue';

            public function chunkSize(): int
            {
                return 100;
            }
        };

        $job = $this->readChunkFor($import);

        $this->assertSame('plain-queue', $job->queue);
    }

    public function test_resolves_to_null_without_a_queue()
    {
        $import = new class implements WithChunkReading {
            public function chunkSize(): int
            {
                return 100;
            }
        };

        $job = $this->readChunkFor($import);

        $this->assertNull($job->queue);
    }

    private function readChunkFor(WithChunkReading $import): ReadChunk
    {
        return new ReadChunk(
            $import,
            new Xlsx(),
            new LocalTemporaryFile(tempnam(sys_get_temp_dir(), 'laravel-excel')),
            'Worksheet',
            $import,
            1,
            100
        );
    }
}
