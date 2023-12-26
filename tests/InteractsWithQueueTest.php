<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Jobs\AppendQueryToSheet;
use Maatwebsite\Excel\Jobs\AppendViewToSheet;
use Maatwebsite\Excel\Jobs\ReadChunk;

class InteractsWithQueueTest extends TestCase
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

    /**
     * @test
     */
    public function append_data_to_sheet_job_can_interact_with_queue()
    {
        $this->assertContains(InteractsWithQueue::class, class_uses(AppendDataToSheet::class));
    }

    /**
     * @test
     */
    public function append_query_to_sheet_job_can_interact_with_queue()
    {
        $this->assertContains(InteractsWithQueue::class, class_uses(AppendQueryToSheet::class));
    }

    /**
     * @test
     */
    public function append_view_to_sheet_job_can_interact_with_queue()
    {
        $this->assertContains(InteractsWithQueue::class, class_uses(AppendViewToSheet::class));
    }
}
