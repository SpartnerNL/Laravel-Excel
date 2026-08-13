<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Closure;
use PhpOffice\PhpSpreadsheet\Calculation\Exception as CalculationException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

trait Readable
{
    /**
     * @throws CalculationException
     */
    public function read(Cell $cell): mixed
    {
        $value = $this->value($cell);

        if ($this->nullable && blank($value)) {
            return null;
        }

        $value = $this->cast($value);

        if ($this->attribute instanceof Closure) {
            return ($this->attribute)($value, $cell);
        }

        return $value;
    }

    /**
     * @throws CalculationException
     */
    protected function value(Cell $cell): mixed
    {
        if ($this->formatted === true) {
            // Without a format of its own the column reads the cell as displayed,
            // which is what WithFormatData asks for.
            if ($this->format !== null) {
                $cell->getStyle()->getNumberFormat()->setFormatCode($this->format);
            }

            return $cell->getFormattedValue();
        }

        return $cell->getCalculatedValue();
    }

    protected function cast(mixed $value): mixed
    {
        return $value;
    }
}
