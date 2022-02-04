<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Text extends Column
{
    protected $type      = DataType::TYPE_STRING;
    protected $format    = NumberFormat::FORMAT_TEXT;
    protected $formatted = true;

    /**
     * Cast to string while exporting.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function toExcelValue($value)
    {
        return (string) $value;
    }

    /**
     * Cast to string while reading.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function cast($value)
    {
        return (string) $value;
    }
}
