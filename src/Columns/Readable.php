<?php

namespace Maatwebsite\Excel\Columns;

use Closure;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

trait Readable
{
    /**
     * @param Cell $cell
     *
     * @return mixed
     */
    public function read(Cell $cell)
    {
        $value = $this->cast(
            $this->value($cell)
        );

        if ($this->attribute instanceof Closure) {
            return ($this->attribute)($value, $cell);
        }

        return $value;
    }

    /**
     * @return mixed
     */
    protected function value(Cell $cell)
    {
        if ($this->formatted) {
            $cell->getStyle()->getNumberFormat()->setFormatCode($this->format);

            return $cell->getFormattedValue();
        }

        return $cell->getCalculatedValue();
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
