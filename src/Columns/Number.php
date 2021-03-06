<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Number extends Column
{
    protected $type = DataType::TYPE_NUMERIC;
    protected $format = NumberFormat::FORMAT_NUMBER;

    public function withDecimals(): Column
    {
        $this->format = NumberFormat::FORMAT_NUMBER_00;

        return $this;
    }

    public function cast($value)
    {
        if ($this->format === NumberFormat::FORMAT_NUMBER) {
            return (int) $value;
        }

        return (float) $value;
    }
}
