<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithExportTemplate;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithTemplate;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

final class WithExportTemplateTest extends TestCase
{
    public function test_can_export_into_a_template(): void
    {
        $export = new class implements FromArray, WithCustomStartCell, WithExportTemplate
        {
            use Exportable;

            public function array(): array
            {
                return [
                    ['Patrick', 'Brouwers'],
                ];
            }

            public function startCell(): string
            {
                return 'A3';
            }

            public function exportTemplate(): Spreadsheet
            {
                $spreadsheet = new Spreadsheet;
                $sheet       = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Report');
                $sheet->setCellValue('A1', 'Users');
                $sheet->setCellValue('C3', '=COUNTA(A3:B3)');
                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

                $spreadsheet->createSheet()->setTitle('Notes')->setCellValue('A1', 'Keep me');

                return $spreadsheet;
            }
        };

        $export->store('with-export-template.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-export-template.xlsx', 'Xlsx');
        $report      = $spreadsheet->getSheet(0);

        $this->assertSame(2, $spreadsheet->getSheetCount());
        $this->assertSame('Report', $report->getTitle());
        $this->assertSame('Users', $report->getCell('A1')->getValue());
        $this->assertSame('Patrick', $report->getCell('A3')->getValue());
        $this->assertSame('Brouwers', $report->getCell('B3')->getValue());
        $this->assertSame('=COUNTA(A3:B3)', $report->getCell('C3')->getValue());
        $this->assertTrue($report->getStyle('A1')->getFont()->getBold());
        $this->assertSame(PageSetup::ORIENTATION_LANDSCAPE, $report->getPageSetup()->getOrientation());
        $this->assertSame('Keep me', $spreadsheet->getSheet(1)->getCell('A1')->getValue());
    }

    public function test_can_export_multiple_sheets_into_a_template(): void
    {
        $export = new class implements WithExportTemplate, WithMultipleSheets
        {
            use Exportable;

            public function sheets(): array
            {
                return [
                    $this->sheetExport('First export'),
                    $this->sheetExport('Second export'),
                    $this->sheetExport('Third export'),
                ];
            }

            public function exportTemplate(): Spreadsheet
            {
                $spreadsheet = new Spreadsheet;
                $spreadsheet->getActiveSheet()->setTitle('First template')->setCellValue('A1', 'First heading');
                $spreadsheet->createSheet()->setTitle('Second template')->setCellValue('A1', 'Second heading');

                return $spreadsheet;
            }

            private function sheetExport(string $value): object
            {
                return new class($value) implements FromArray, WithCustomStartCell
                {
                    public function __construct(
                        private string $value,
                    ) {
                    }

                    public function array(): array
                    {
                        return [[$this->value]];
                    }

                    public function startCell(): string
                    {
                        return 'A3';
                    }
                };
            }
        };

        $export->store('multiple-sheets-with-export-template.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/multiple-sheets-with-export-template.xlsx', 'Xlsx');

        $this->assertSame(3, $spreadsheet->getSheetCount());
        $this->assertSame('First heading', $spreadsheet->getSheet(0)->getCell('A1')->getValue());
        $this->assertSame('First export', $spreadsheet->getSheet(0)->getCell('A3')->getValue());
        $this->assertSame('Second heading', $spreadsheet->getSheet(1)->getCell('A1')->getValue());
        $this->assertSame('Second export', $spreadsheet->getSheet(1)->getCell('A3')->getValue());
        $this->assertSame('Third export', $spreadsheet->getSheet(2)->getCell('A3')->getValue());
    }

    public function test_can_queue_an_export_into_a_template(): void
    {
        $export = new QueuedExportWithTemplate;

        $export->queue('queued-with-export-template.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/queued-with-export-template.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertSame('Queued users', $sheet->getCell('A1')->getValue());
        $this->assertSame('Patrick', $sheet->getCell('A3')->getValue());
        $this->assertSame('Brouwers', $sheet->getCell('B3')->getValue());
    }
}
