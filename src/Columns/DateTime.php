<?php

namespace Maatwebsite\Excel\Columns;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DateTime extends Date
{
    protected $format = NumberFormat::FORMAT_DATE_DATETIME;
    protected $time = true;
}