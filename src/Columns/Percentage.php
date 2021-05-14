<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Percentage extends Column
{
    protected $type   = DataType::TYPE_NUMERIC;
    protected $format = NumberFormat::FORMAT_PERCENTAGE;
}
