<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Closure;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class CellStyle
{
    use Styleable;

    public function apply(Cell $cell, mixed $data, ?Closure $callback = null): void
    {
        if (!$callback instanceof Closure) {
            return;
        }

        $callback($this, $data);

        if ($this->style === null) {
            return;
        }

        $cell->getStyle()->applyFromArray($this->style);
    }
}
