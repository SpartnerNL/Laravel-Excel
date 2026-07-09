<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Tests\Data\Stubs\BeforeExportListener;
use Maatwebsite\Excel\Tests\Data\Stubs\ExportWithEvents;
use Maatwebsite\Excel\Tests\Data\Stubs\ExportWithRegistersEventListeners;
use Maatwebsite\Excel\Tests\Data\Stubs\ImportWithRegistersEventListeners;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class RegistersEventListenersTest extends TestCase
{
    public function test_events_get_called_when_exporting(): void
    {
        $event = new ExportWithRegistersEventListeners;

        $eventsTriggered = 0;

        $event::$beforeExport = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->writer);
            $eventsTriggered++;
        };

        $event::$beforeWriting = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeWriting::class, $event);
            $this->assertInstanceOf(Writer::class, $event->writer);
            $eventsTriggered++;
        };

        $event::$beforeSheet = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $event::$afterSheet = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
        $this->assertSame(4, $eventsTriggered);
    }

    public function test_events_get_called_when_importing(): void
    {
        $event = new ImportWithRegistersEventListeners;

        $eventsTriggered = 0;

        $event::$beforeImport = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->reader);
            $eventsTriggered++;
        };

        $event::$beforeSheet = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $event::$afterSheet = function ($event) use (&$eventsTriggered): void {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $event->import('import.xlsx');
        $this->assertSame(3, $eventsTriggered);
    }

    public function test_can_have_invokable_class_as_listener(): void
    {
        $event = new ExportWithEvents;

        $event->beforeExport = new BeforeExportListener(function ($event): void {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->writer);
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
    }
}
