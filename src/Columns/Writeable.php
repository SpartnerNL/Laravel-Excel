<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Writeable
{
    public function beforeWriting(Worksheet $worksheet)
    {
        $this->formatColumn($worksheet);
        $this->writeStyles($worksheet);
    }

    /**
     * @param Worksheet $sheet
     * @param int       $row
     * @param mixed     $data
     *
     * @return Cell
     */
    public function write(Worksheet $sheet, int $row, $data)
    {
        $cell = $sheet->getCellByColumnAndRow($this->index, $row);

        if ($this->type) {
            $cell->setDataType($this->type);
        }

        if ($this->format) {
            $cell->getStyle()->getNumberFormat()->setFormatCode($this->format);
        }

        $value = $this->resolveValue($data);

        $this->writeValue($cell, $value);
        $this->writeComment($cell, $data);
        $this->writeCellStyle($cell, $data);

        return $cell;
    }

    public function afterWriting(Worksheet $worksheet)
    {
        $this->writeSize($worksheet);
        $this->writeFilters($worksheet);
    }

    protected function writeValue(Cell $cell, $value)
    {
        $this->type
            ? $cell->setValueExplicit($value, $this->type)
            : $cell->setValue($value);
    }
}
