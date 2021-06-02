<?php

namespace Maatwebsite\Excel\Columns;

use Maatwebsite\Excel\ImageContent;
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

    /**
     * @return mixed
     */
    protected function value(Cell $cell)
    {
        foreach ($cell->getParent()->getParent()->getDrawingCollection() as $drawing) {
            if ($drawing->getCoordinates() === $cell->getCoordinate()) {
                return ImageContent::from($drawing);
            }
        }

        return null;
    }

    protected function writeValue(Worksheet $worksheet, Cell $cell, $value): void
    {
        $drawing = new Drawing();
        $drawing->setCoordinates($cell->getCoordinate());
        $drawing->setPath(realpath($value));
        $drawing->setWorksheet($worksheet);

        if ($this->height) {
            $drawing->setHeight($this->height);
            $worksheet->getRowDimension($cell->getRow())->setRowHeight($this->height);
        }

        if ($this->width) {
            $drawing->setWidth($this->width);
        }
    }
}
