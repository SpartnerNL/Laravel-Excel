<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Boolean extends Column
{
    protected ?string $type = DataType::TYPE_BOOL;

    protected function cast(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected function toExcelValue(mixed $value): bool
    {
        return $this->cast($value);
    }
}
