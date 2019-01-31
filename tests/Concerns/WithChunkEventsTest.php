<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Maatwebsite\Excel\Events\AfterChunkImport;
use Maatwebsite\Excel\Events\BeforeChunkImport;
use Maatwebsite\Excel\Tests\Data\Stubs\ImportWithChunkEvents;

class WithChunkEventsTest extends TestCase
{
    /**
     * @test
     */
    public function import_chunk_events_get_called()
    {
        $event = new ImportWithChunkEvents();

        $eventsTriggered = 0;

        $event->beforeChunkImport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeChunkImport::class, $event);
            $this->assertInstanceOf(IReader::class, $event->getReader());
            $eventsTriggered++;
        };

        $event->afterChunkImport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterChunkImport::class, $event);
            $this->assertInstanceOf(IReader::class, $event->getReader());
            $eventsTriggered++;
        };

        $event->import('import.xlsx');
        $this->assertEquals(2, $eventsTriggered);
    }
}
