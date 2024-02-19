<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Cell;
use Maatwebsite\Excel\Middleware\ConvertEmptyCellValuesToNull;
use Maatwebsite\Excel\Middleware\TrimCellValue;

class CellTest extends TestCase
{
    public function test_can_get_cell_value()
    {
        config()->set('excel.imports.cells.middleware', []);

        $worksheet = $this->read(__DIR__ . '/Data/Disks/Local/import-middleware.xlsx', 'Xlsx');

        $this->assertEquals('test', Cell::make($worksheet->getActiveSheet(), 'A1')->getValue());

        // By default spaces are not removed
        $this->assertEquals('       ', Cell::make($worksheet->getActiveSheet(), 'A2')->getValue());
    }

    public function test_can_trim_empty_cells()
    {
        config()->set('excel.imports.cells.middleware', [
            TrimCellValue::class,
        ]);

        $worksheet = $this->read(__DIR__ . '/Data/Disks/Local/import-middleware.xlsx', 'Xlsx');

        $this->assertEquals('', Cell::make($worksheet->getActiveSheet(), 'A2')->getValue());

        config()->set('excel.imports.cells.middleware', []);
    }

    public function test_convert_empty_cells_to_null()
    {
        config()->set('excel.imports.cells.middleware', [
            TrimCellValue::class,
            ConvertEmptyCellValuesToNull::class,
        ]);

        $worksheet = $this->read(__DIR__ . '/Data/Disks/Local/import-middleware.xlsx', 'Xlsx');

        $this->assertEquals(null, Cell::make($worksheet->getActiveSheet(), 'A2')->getValue());

        config()->set('excel.imports.cells.middleware', []);
    }
}
