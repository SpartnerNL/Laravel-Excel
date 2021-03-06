<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DateTime extends Date
{
    protected $format = NumberFormat::FORMAT_DATE_DATETIME;
    protected $time   = true;
}
