<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DateTime extends Date
{
    protected ?string $format = NumberFormat::FORMAT_DATE_DATETIME;

    protected bool $time = true;
}
