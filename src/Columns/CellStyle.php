<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

class CellStyle
{
    use Styleable;

    public function apply(Cell $cell, $data, callable $callback = null)
    {
        if (!is_callable($callback)) {
            return;
        }

        $callback($this, $data);

        if (!$this->style) {
            return;
        }

        $cell->getStyle()->applyFromArray($this->style);
    }
}