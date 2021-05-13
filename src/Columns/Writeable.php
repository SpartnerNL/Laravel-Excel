<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Writeable
{
    public function beforeWriting(Worksheet $worksheet): void
    {
        $this->formatColumn($worksheet);
        $this->writeStyles($worksheet);
    }

    /**
     * @param mixed $data
     */
    public function write(Worksheet $sheet, int $row, $data): Cell
    {
        $cell = $sheet->getCellByColumnAndRow($this->index, $row);

        if ($this->type) {
            $cell->setDataType($this->type);
        }

        $value = $this->resolveValue($data);

        $this->writeValue($cell, $value);
        $this->writeCellStyle($cell, $data);

        return $cell;
    }

    public function afterWriting(Worksheet $worksheet): void
    {
        $this->writeSize($worksheet);
        $this->writeFilters($worksheet);
    }

    protected function writeValue(Cell $cell, $value): void
    {
        $this->type
            ? $cell->setValueExplicit($value, $this->type)
            : $cell->setValue($value);
    }
}
