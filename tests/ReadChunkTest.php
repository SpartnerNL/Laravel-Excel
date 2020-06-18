<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Jobs\ReadChunk;

class ReadChunkTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function read_chunk_job_can_interact_with_queue()
    {
        $this->assertContains(InteractsWithQueue::class, class_uses(ReadChunk::class));
    }
}
