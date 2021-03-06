<?php

namespace Maatwebsite\Excel\Columns;

use Carbon\Carbon;
use DateTime;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Date extends Column
{
    protected $type   = DataType::TYPE_NUMERIC;
    protected $format = NumberFormat::FORMAT_DATE_DDMMYYYY;
    protected $time   = false;

    public function toExcelValue($value)
    {
        // If the value is an integer, the user
        // most likely formatted the date themselves.
        if (is_int($value)) {
            return $value;
        }

        if ($value instanceof DateTime) {
            return ExcelDate::dateTimeToExcel($value);
        }

        return ExcelDate::stringToExcel($value);
    }

    public function cast($value)
    {
        $date = Carbon::parse(
            ExcelDate::excelToDateTimeObject($value)
        );

        if (!$this->time) {
            $date->setHour(0)->setMinute(0)->setSecond(0);
        }

        return $date;
    }
}
