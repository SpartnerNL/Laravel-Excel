<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Text extends Column
{
    protected $type      = DataType::TYPE_STRING;
    protected $format    = NumberFormat::FORMAT_TEXT;
    protected $formatted = true;
}
