<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\FromUsersQueryExportWithColumns;
use Maatwebsite\Excel\Tests\Data\Stubs\WithColumnsMultipleSheetsExport;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class WithColumnsQueuedExportTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);

        User::factory()->count(100)->create();
    }

    public function test_can_queue_an_export_with_columns(): void
    {
        $export = new FromUsersQueryExportWithColumns;

        $export->queue('queued-columns-export.xlsx');

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/queued-columns-export.xlsx', 'Xlsx');

        // Heading row plus every user; the columns have to survive into the
        // append jobs, which build their own Sheet instance.
        $this->assertCount(101, $actual);
        $this->assertSame(['ID', 'Name', 'Email'], $actual[0]);

        $names = User::query()->orderBy('id')->pluck('name')->all();

        $this->assertSame($names[0], $actual[1][1]);
        $this->assertSame($names[99], $actual[100][1]);
    }

    public function test_queued_export_with_columns_applies_formats_widths_and_filters(): void
    {
        $export = new FromUsersQueryExportWithColumns;

        $export->queue('queued-columns-export.xlsx');

        $sheet = $this->read(__DIR__ . '/../Data/Disks/Local/queued-columns-export.xlsx', 'Xlsx')->getActiveSheet();

        // afterWriting() has to run in CloseSheet, once every chunk is written.
        $this->assertSame(
            NumberFormat::FORMAT_NUMBER,
            $sheet->getCell('A2')->getStyle()->getNumberFormat()->getFormatCode()
        );

        $this->assertEqualsWithDelta(30.0, $sheet->getColumnDimension('B')->getWidth(), PHP_FLOAT_EPSILON);

        // The range can only be right if getHighestRow() saw all 100 rows.
        $this->assertSame('A1:A101', $sheet->getAutoFilter()->getRange());
    }

    public function test_queued_export_does_not_bleed_column_formats_onto_the_heading(): void
    {
        $export = new FromUsersQueryExportWithColumns;

        $export->queue('queued-columns-export.xlsx');

        $sheet = $this->read(__DIR__ . '/../Data/Disks/Local/queued-columns-export.xlsx', 'Xlsx')->getActiveSheet();

        $this->assertSame(
            NumberFormat::FORMAT_GENERAL,
            $sheet->getCell('A1')->getStyle()->getNumberFormat()->getFormatCode()
        );
    }

    public function test_can_queue_a_multi_sheet_export_with_columns(): void
    {
        $export = new WithColumnsMultipleSheetsExport;

        $export->queue('queued-columns-multiple-sheets.xlsx');

        $path = __DIR__ . '/../Data/Disks/Local/queued-columns-multiple-sheets.xlsx';

        $first  = $this->readAsArray($path, 'Xlsx', 0);
        $second = $this->readAsArray($path, 'Xlsx', 1);

        $this->assertSame(['ID', 'Name', 'Email'], $first[0]);
        $this->assertCount(101, $first);

        $this->assertSame(['Email'], $second[0]);
        $this->assertCount(101, $second);
    }
}
