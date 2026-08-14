<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Time extends Date
{
    protected ?string $format = NumberFormat::FORMAT_DATE_TIME4;

    protected bool $time = true;
}
