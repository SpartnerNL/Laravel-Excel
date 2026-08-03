<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithExportTemplate;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Tests\Data\Stubs\QueuedExportWithTemplate;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use RuntimeException;

final class WithExportTemplateTest extends TestCase
{
    public function test_can_export_into_a_template(): void
    {
        $template = new Spreadsheet;
        $sheet    = $template->getActiveSheet();
        $sheet->setTitle('Report');
        $sheet->setCellValue('A1', 'Users');
        $sheet->setCellValue('C3', '=COUNTA(A3:B3)');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $template->createSheet()->setTitle('Notes')->setCellValue('A1', 'Keep me');

        $export = new class($this->saveTemplate($template)) implements FromArray, WithCustomStartCell, WithExportTemplate
        {
            use Exportable;

            public function __construct(
                private string $templatePath,
            ) {
            }

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

            public function exportTemplate(): string
            {
                return $this->templatePath;
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

    public function test_can_append_multiple_chunks_with_custom_start_cell_and_export_template(): void
    {
        $template = new Spreadsheet;
        $template->getActiveSheet()->setCellValue('A1', 'Users');

        $export = new class($this->saveTemplate($template)) implements FromCollection, WithCustomStartCell, WithExportTemplate
        {
            use Exportable;

            public function __construct(
                private string $templatePath,
            ) {
            }

            /**
             * @return Collection<int, array{int}>
             */
            public function collection(): Collection
            {
                return new Collection(array_map(
                    fn (int $row): array => [$row],
                    range(1, 1001)
                ));
            }

            public function startCell(): string
            {
                return 'B2';
            }

            public function exportTemplate(): string
            {
                return $this->templatePath;
            }
        };

        $export->store('custom-start-cell-with-export-template.xlsx');

        $spreadsheet = $this->read(
            __DIR__ . '/../Data/Disks/Local/custom-start-cell-with-export-template.xlsx',
            'Xlsx'
        );
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('Users', $sheet->getCell('A1')->getValue());
        $this->assertSame(1, $sheet->getCell('B2')->getValue());
        $this->assertSame(1000, $sheet->getCell('B1001')->getValue());
        $this->assertSame(1001, $sheet->getCell('B1002')->getValue());
    }

    public function test_can_export_multiple_sheets_into_a_template(): void
    {
        $template = new Spreadsheet;
        $template->getActiveSheet()->setTitle('First template')->setCellValue('A1', 'First heading');
        $template->createSheet()->setTitle('Second template')->setCellValue('A1', 'Second heading');

        $export = new class($this->saveTemplate($template)) implements WithExportTemplate, WithMultipleSheets
        {
            use Exportable;

            public function __construct(
                private string $templatePath,
            ) {
            }

            public function sheets(): array
            {
                return [
                    $this->sheetExport('First export'),
                    $this->sheetExport('Second export'),
                    $this->sheetExport('Third export'),
                ];
            }

            public function exportTemplate(): string
            {
                return $this->templatePath;
            }

            private function sheetExport(string $value): object
            {
                return new readonly class($value) implements FromArray, WithCustomStartCell
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

    public function test_can_queue_an_export_into_a_template_across_multiple_chunks(): void
    {
        $template = new Spreadsheet;
        $template->getActiveSheet()->setCellValue('A1', 'Queued users');

        $export = new QueuedExportWithTemplate($this->saveTemplate($template));

        $export->queue('queued-with-export-template.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/queued-with-export-template.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertSame('Queued users', $sheet->getCell('A1')->getValue());
        $this->assertSame('Patrick', $sheet->getCell('A3')->getValue());
        $this->assertSame('Brouwers', $sheet->getCell('B3')->getValue());
        $this->assertSame('Taylor', $sheet->getCell('A4')->getValue());
        $this->assertSame('Otwell', $sheet->getCell('B4')->getValue());
    }

    private function saveTemplate(Spreadsheet $spreadsheet): string
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'laravel-excel-template-');
        if ($templatePath === false) {
            throw new RuntimeException('Unable to create the template file.');
        }

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($templatePath);

        return $templatePath;
    }
}
