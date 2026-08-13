<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Calculation\Exception as CalculationException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Formula extends Column
{
    protected ?string $type = DataType::TYPE_FORMULA;

    protected bool $calculate = false;

    /**
     * @throws CalculationException
     */
    public function read(Cell $cell): mixed
    {
        if ($this->type !== null) {
            $cell->setDataType($this->type);
        }

        if ($this->calculate) {
            return $cell->getCalculatedValue();
        }

        return $cell->getValue();
    }

    public function calculated(): static
    {
        $this->calculate = true;

        return $this;
    }
}
