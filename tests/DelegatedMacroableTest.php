<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Writer;
use PhpOffice\PhpSpreadsheet\Document\Properties;

final class DelegatedMacroableTest extends TestCase
{
    public function test_can_call_methods_from_delegate(): void
    {
        $export = new class implements Export, WithEvents
        {
            use Exportable, RegistersEventListeners;

            public static function beforeExport(BeforeExport $event): void
            {
                // ->getProperties() will be called via __call on the ->getDelegate()
                TestCase::assertInstanceOf(Properties::class, $event->writer->getProperties());
            }
        };

        $export->download('some-file.xlsx');
    }

    public function test_can_use_writer_macros(): void
    {
        $called = false;
        Writer::macro('test', function () use (&$called): void {
            $called = true;
        });

        $export = new class implements Export, WithEvents
        {
            use Exportable, RegistersEventListeners;

            public static function beforeExport(BeforeExport $event): void
            {
                // call macro method
                /** @phpstan-ignore method.notFound */
                $event->writer->test();
            }
        };

        $export->download('some-file.xlsx');

        $this->assertTrue($called);
    }

    public function test_can_use_sheet_macros(): void
    {
        $called = false;
        Sheet::macro('test', function () use (&$called): void {
            $called = true;
        });

        $export = new class implements Export, WithEvents
        {
            use Exportable, RegistersEventListeners;

            public static function beforeSheet(BeforeSheet $event): void
            {
                // call macro method
                /** @phpstan-ignore method.notFound */
                $event->sheet->test();
            }
        };

        $export->download('some-file.xlsx');

        $this->assertTrue($called);
    }
}
