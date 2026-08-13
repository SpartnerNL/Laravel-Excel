<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Decimal extends Column
{
    protected ?string $type = DataType::TYPE_NUMERIC;

    protected ?string $format = NumberFormat::FORMAT_NUMBER_00;

    protected function cast(mixed $value): float
    {
        return (float) $value;
    }

    protected function toExcelValue(mixed $value): float
    {
        return (float) $value;
    }
}
