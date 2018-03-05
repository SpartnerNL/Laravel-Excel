<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Writer;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Tests\Data\Stubs\ExportWithEvents;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Tests\Data\Stubs\BeforeExportListener;

class WithEventsTest extends TestCase
{
    /**
     * @test
     */
    public function events_get_called()
    {
        $event = new ExportWithEvents();

        $event->beforeExport = function ($event) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->writer);
        };

        $event->beforeWriting = function ($event) {
            $this->assertInstanceOf(BeforeWriting::class, $event);
            $this->assertInstanceOf(Writer::class, $event->writer);
        };

        $event->beforeSheet = function ($event) {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
        };

        $event->afterSheet = function ($event) {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
        };

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
    }

    /**
     * @test
     */
    public function can_have_invokable_class_as_listener()
    {
        $event = new ExportWithEvents();

        $event->beforeExport = new BeforeExportListener(function ($event) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->writer);
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
    }
}
