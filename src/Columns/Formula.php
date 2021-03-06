<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Formula extends Column
{
    protected $type = DataType::TYPE_FORMULA;
    protected $calculate = false;

    public function read(Cell $cell)
    {
        if ($this->type) {
            $cell->setDataType($this->type);
        }

        if ($this->calculate) {
            return $cell->getCalculatedValue();
        }

        return $cell->getValue();
    }

    public function calculated(): Column
    {
        $this->calculate = true;

        return $this;
    }
}
