<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Boolean extends Column
{
    protected $type = DataType::TYPE_BOOL;

    /**
     * @param mixed $value
     *
     * @return bool
     */
    protected function cast($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    protected function toExcelValue($value): bool
    {
        return $this->cast($value);
    }
}
