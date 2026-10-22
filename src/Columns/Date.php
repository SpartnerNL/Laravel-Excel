<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Carbon\Carbon;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Date extends Column
{
    protected ?string $type = DataType::TYPE_NUMERIC;

    protected ?string $format = NumberFormat::FORMAT_DATE_DDMMYYYY;

    protected bool $time = false;

    protected function toExcelValue(mixed $value): int|float
    {
        // If the value is a number, the user most likely
        // converted the date to an Excel serial themselves.
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return ExcelDate::dateTimeToExcel($value);
        }

        return (float) ExcelDate::stringToExcel((string) $value);
    }

    protected function cast(mixed $value): Carbon
    {
        $date = Carbon::instance(
            ExcelDate::excelToDateTimeObject((float) $value)
        );

        if (!$this->time) {
            $date->setTime(0, 0);
        }

        return $date;
    }
}
