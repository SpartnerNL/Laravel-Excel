<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Cell;
use Maatwebsite\Excel\Middleware\ConvertEmptyCellValuesToNull;
use Maatwebsite\Excel\Middleware\TrimCellValue;
use Mockery;
use PhpOffice\PhpSpreadsheet\Calculation\Exception as CalculationException;
use PhpOffice\PhpSpreadsheet\Cell\Cell as SpreadsheetCell;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class CellTest extends TestCase
{
    public function test_can_get_cell_value(): void
    {
        config()->set('excel.imports.cells.middleware', []);

        $worksheet = $this->read(__DIR__ . '/Data/Disks/Local/import-middleware.xlsx', 'Xlsx');

        $this->assertSame('test', Cell::make($worksheet->getActiveSheet(), 'A1')->getValue());

        // By default spaces are not removed
        $this->assertSame('       ', Cell::make($worksheet->getActiveSheet(), 'A2')->getValue());
    }

    public function test_can_trim_empty_cells(): void
    {
        config()->set('excel.imports.cells.middleware', [
            TrimCellValue::class,
        ]);

        $worksheet = $this->read(__DIR__ . '/Data/Disks/Local/import-middleware.xlsx', 'Xlsx');

        $this->assertSame('', Cell::make($worksheet->getActiveSheet(), 'A2')->getValue());

        config()->set('excel.imports.cells.middleware', []);
    }

    public function test_convert_empty_cells_to_null(): void
    {
        config()->set('excel.imports.cells.middleware', [
            TrimCellValue::class,
            ConvertEmptyCellValuesToNull::class,
        ]);

        $worksheet = $this->read(__DIR__ . '/Data/Disks/Local/import-middleware.xlsx', 'Xlsx');

        $this->assertNull(Cell::make($worksheet->getActiveSheet(), 'A2')->getValue());

        config()->set('excel.imports.cells.middleware', []);
    }

    public function test_trim_passes_non_string_values_through_unchanged(): void
    {
        $middleware = new TrimCellValue;

        $this->assertSame(42, $middleware(42, fn ($v) => $v));
        $this->assertNull($middleware(null, fn ($v) => $v));
    }

    public function test_trim_removes_bom_characters(): void
    {
        $middleware = new TrimCellValue;

        $this->assertSame('test', $middleware("\xEF\xBB\xBFtest", fn ($v) => $v));
    }

    public function test_trim_removes_zero_width_spaces(): void
    {
        $middleware = new TrimCellValue;

        $this->assertSame('test', $middleware("\u{200B}test\u{200B}", fn ($v) => $v));
    }

    public function test_get_delegate_returns_the_underlying_spreadsheet_cell(): void
    {
        $worksheet       = (new Spreadsheet)->getActiveSheet();
        $spreadsheetCell = $worksheet->getCell('A1');

        $this->assertSame($spreadsheetCell, Cell::make($worksheet, 'A1')->getDelegate());
    }

    public function test_get_value_returns_plain_text_for_rich_text_value(): void
    {
        $worksheet = (new Spreadsheet)->getActiveSheet();
        $richText  = new RichText;
        $richText->createText('hello world');
        $worksheet->getCell('A1')->setValue($richText);

        $this->assertSame('hello world', Cell::make($worksheet, 'A1')->getValue());
    }

    public function test_get_value_falls_back_to_old_calculated_value_when_calculation_throws(): void
    {
        $spreadsheetCell = Mockery::mock(SpreadsheetCell::class);
        $spreadsheetCell->shouldReceive('getValue')->andReturn('=INVALID()');
        $spreadsheetCell->shouldReceive('getCalculatedValue')->andThrow(new CalculationException('bad formula'));
        $spreadsheetCell->shouldReceive('getOldCalculatedValue')->andReturn('cached-value');

        $cell = new Cell($spreadsheetCell);

        $this->assertSame('cached-value', $cell->getValue(null, true, false));
    }
}
