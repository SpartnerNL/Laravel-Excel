<?php

declare(strict_types=1);

namespace Maatwebsite\Excel;

use Override;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder as PhpSpreadsheetDefaultValueBinder;

class DefaultValueBinder extends PhpSpreadsheetDefaultValueBinder
{
    /**
     * @param  Cell  $cell  Cell to bind value to
     * @param  mixed  $value  Value to bind in cell
     */
    #[Override]
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_array($value)) {
            $value = \json_encode($value);
        }

        return parent::bindValue($cell, $value);
    }
}
