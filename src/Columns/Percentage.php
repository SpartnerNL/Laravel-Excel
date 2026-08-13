<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Percentage extends Column
{
    protected ?string $type = DataType::TYPE_NUMERIC;

    protected ?string $format = NumberFormat::FORMAT_PERCENTAGE;

    protected bool $wholeNumbers = false;

    /**
     * Read and write percentages as whole numbers (10 for 10%) rather than as the
     * fractions Excel stores them in (0.1 for 10%).
     */
    public function fromWholeNumbers(): static
    {
        $this->wholeNumbers = true;

        return $this;
    }

    protected function toExcelValue(mixed $value): float
    {
        return $this->wholeNumbers ? (float) $value / 100 : (float) $value;
    }

    protected function cast(mixed $value): float
    {
        return $this->wholeNumbers ? (float) $value * 100 : (float) $value;
    }
}
