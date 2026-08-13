<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Price extends Column
{
    protected ?string $type = DataType::TYPE_NUMERIC;

    protected ?string $format = NumberFormat::FORMAT_NUMBER;

    public function currency(string $currency): static
    {
        return $this->format($currency);
    }

    public function inEuros(): static
    {
        return $this->currency(NumberFormat::FORMAT_ACCOUNTING_EUR);
    }

    public function inDollars(): static
    {
        return $this->currency(NumberFormat::FORMAT_ACCOUNTING_USD);
    }
}
