<?php

namespace Maatwebsite\Excel;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder as PhpSpreadsheetDefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;

/**
 * phpspreadsheet 2.0 added a native `: bool` return type to IValueBinder::bindValue().
 * Two class definitions are needed to satisfy both interface versions without
 * forcing a breaking signature change on downstream subclasses.
 *
 * @see https://github.com/PHPOffice/PhpSpreadsheet/releases/tag/2.0.0
 */
if ((new \ReflectionMethod(IValueBinder::class, 'bindValue'))->hasReturnType()) {
    class DefaultValueBinder extends PhpSpreadsheetDefaultValueBinder
    {
        /**
         * @param  Cell  $cell  Cell to bind value to
         * @param  mixed  $value  Value to bind in cell
         */
        public function bindValue(Cell $cell, $value): bool
        {
            if (is_array($value)) {
                $value = \json_encode($value);
            }

            return parent::bindValue($cell, $value);
        }
    }
} else {
    class DefaultValueBinder extends PhpSpreadsheetDefaultValueBinder
    {
        /**
         * @param  Cell  $cell  Cell to bind value to
         * @param  mixed  $value  Value to bind in cell
         * @return bool
         */
        public function bindValue(Cell $cell, $value)
        {
            if (is_array($value)) {
                $value = \json_encode($value);
            }

            return parent::bindValue($cell, $value);
        }
    }
}
