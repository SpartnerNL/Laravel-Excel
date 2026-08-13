<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Text extends Column
{
    protected ?string $type = DataType::TYPE_STRING;

    protected ?string $format = NumberFormat::FORMAT_TEXT;

    protected ?bool $formatted = true;

    /**
     * Cast to string while exporting.
     */
    protected function toExcelValue(mixed $value): string
    {
        return (string) $value;
    }

    /**
     * Cast to string while reading.
     */
    protected function cast(mixed $value): string
    {
        return (string) $value;
    }
}
