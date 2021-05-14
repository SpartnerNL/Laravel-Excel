<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Readable
{
    /**
     * @param Cell $cell
     *
     * @return mixed
     */
    public function read(Cell $cell)
    {
        if ($this->formatted) {
            $cell->getStyle()->getNumberFormat()->setFormatCode($this->format);

            $value = $cell->getFormattedValue();
        } else {
            $value = $cell->getCalculatedValue();
        }

        $value = $this->cast($value);

        if (is_callable($this->attribute)) {
            return ($this->attribute)($value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function cast($value)
    {
        return $value;
    }
}
