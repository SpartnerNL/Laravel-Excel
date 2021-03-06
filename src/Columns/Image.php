<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Image extends Column
{
    /**
     * @var int|null
     */
    protected $height;

    /**
     * @param int|null $height
     *
     * @return $this
     */
    public function height(int $height)
    {
        $this->height = $height;

        return $this;
    }

    protected function writeValue(Worksheet $worksheet, Cell $cell, $value)
    {
        $drawing = new Drawing();
        $drawing->setCoordinates($cell->getCoordinate());
        $drawing->setPath(realpath($value));

        if ($this->height) {
            $drawing->setHeight($this->height);
            $worksheet->getRowDimension($cell->getRow())->setRowHeight($this->height);
        }

        if ($this->width) {
            $drawing->setWidth($this->width);
        }

        $drawing->setWorksheet($worksheet);
    }
}