<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Percentage extends Column
{
    protected ?string $type = DataType::TYPE_NUMERIC;

    protected ?string $format = NumberFormat::FORMAT_PERCENTAGE;
}
