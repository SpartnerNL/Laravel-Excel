<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeRead;
use Maatwebsite\Excel\Events\AfterRead;
use Maatwebsite\Excel\Events\BeforeImport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Tests\Data\Stubs\ImportWithRegistersEventListeners;

class RegistersImportEventListenersTest extends TestCase
{
    /**
     * @test
     */
    public function events_get_called_when_importing()
    {
        $event = new ImportWithRegistersEventListeners();

        $eventsTriggered = 0;

        $event::$beforeRead = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeRead::class, $event);
            $this->assertInstanceOf(Reader::class, $event->reader);
            $eventsTriggered++;
        };

        $event::$afterRead = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterRead::class, $event);
            $this->assertInstanceOf(Reader::class, $event->reader);
            $eventsTriggered++;
        };

        $event::$beforeImport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->reader);
            $eventsTriggered++;
        };

        $event::$beforeSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $event::$afterSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $event->import('import.xlsx');
        $this->assertEquals(5, $eventsTriggered);
    }
}
