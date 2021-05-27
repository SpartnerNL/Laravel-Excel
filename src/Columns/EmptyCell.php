<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class EmptyCell extends Column
{
    protected $type = DataType::TYPE_NULL;

    /**
     * @param string|null          $title
     * @param string|callable|null $attribute
     *
     * @return $this
     */
    public static function make(string $title = null, $attribute = null): Column
    {
        return parent::make($title ?: '', $attribute);
    }

    public function read(Cell $cell)
    {
        return null;
    }
}
