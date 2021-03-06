<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Decimal extends Column
{
    protected $type   = DataType::TYPE_NUMERIC;
    protected $format = NumberFormat::FORMAT_NUMBER_00;

    public function cast($value)
    {
        return (float) $value;
    }
}
