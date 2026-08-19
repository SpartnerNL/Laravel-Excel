<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Events;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Events\AfterBatch;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Imports\ModelManager;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Writer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class EventsTest extends TestCase
{
    public function test_after_batch_getters_return_the_given_values(): void
    {
        $manager    = app(ModelManager::class);
        $importable = new class implements Import
        {
        };

        $event = new AfterBatch($manager, $importable, 50, 10);

        $this->assertSame($manager, $event->manager);
        $this->assertSame($manager, $event->getManager());
        $this->assertSame($manager, $event->getDelegate());
        $this->assertSame(50, $event->getBatchSize());
        $this->assertSame(10, $event->getStartRow());
        $this->assertSame($importable, $event->getConcernable());
    }

    public function test_before_export_getters_return_the_given_writer(): void
    {
        $writer     = app(Writer::class);
        $exportable = new class implements Export
        {
        };

        $event = new BeforeExport($writer, $exportable);

        $this->assertSame($writer, $event->writer);
        $this->assertSame($writer, $event->getWriter());
        $this->assertSame($writer, $event->getDelegate());
        $this->assertSame($exportable, $event->getConcernable());
    }

    public function test_before_import_getters_return_the_given_reader(): void
    {
        $reader     = app(Reader::class);
        $importable = new class implements Import
        {
        };

        $event = new BeforeImport($reader, $importable);

        $this->assertSame($reader, $event->reader);
        $this->assertSame($reader, $event->getReader());
        $this->assertSame($reader, $event->getDelegate());
        $this->assertSame($importable, $event->getConcernable());
    }

    public function test_after_import_getters_return_the_given_reader(): void
    {
        $reader     = app(Reader::class);
        $importable = new class implements Import
        {
        };

        $event = new AfterImport($reader, $importable);

        $this->assertSame($reader, $event->reader);
        $this->assertSame($reader, $event->getReader());
        $this->assertSame($reader, $event->getDelegate());
        $this->assertSame($importable, $event->getConcernable());
    }

    public function test_before_sheet_getters_return_the_given_sheet(): void
    {
        $sheet       = new Sheet((new Spreadsheet)->getActiveSheet());
        $concernable = new class implements Export
        {
        };

        $event = new BeforeSheet($sheet, $concernable);

        $this->assertSame($sheet, $event->sheet);
        $this->assertSame($sheet, $event->getSheet());
        $this->assertSame($sheet, $event->getDelegate());
        $this->assertSame($concernable, $event->getConcernable());
    }

    public function test_after_chunk_getters_return_the_given_values(): void
    {
        $sheet       = new Sheet((new Spreadsheet)->getActiveSheet());
        $concernable = new class implements Import
        {
        };

        $event = new AfterChunk($sheet, $concernable, 25);

        $this->assertSame($sheet, $event->getSheet());
        $this->assertSame($sheet, $event->getDelegate());
        $this->assertSame(25, $event->getStartRow());
        $this->assertSame($concernable, $event->getConcernable());
    }

    public function test_applies_to_concern_checks_the_concernable_type(): void
    {
        $sheet       = new Sheet((new Spreadsheet)->getActiveSheet());
        $concernable = new class implements Export
        {
        };

        $event = new BeforeSheet($sheet, $concernable);

        $this->assertTrue($event->appliesToConcern($concernable::class));
        $this->assertFalse($event->appliesToConcern(Import::class));
    }
}
