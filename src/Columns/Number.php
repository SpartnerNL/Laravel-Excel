<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Number extends Column
{
    protected ?string $type = DataType::TYPE_NUMERIC;

    protected ?string $format = NumberFormat::FORMAT_NUMBER;

    public function withDecimals(): static
    {
        $this->format = NumberFormat::FORMAT_NUMBER_00;

        return $this;
    }

    protected function toExcelValue(mixed $value): int|float
    {
        return $this->cast($value);
    }

    protected function cast(mixed $value): int|float
    {
        if ($this->format === NumberFormat::FORMAT_NUMBER) {
            return (int) $value;
        }

        return (float) $value;
    }
}
