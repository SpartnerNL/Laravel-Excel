<?php

namespace Maatwebsite\Excel;

use Illuminate\Pipeline\Pipeline;
use PhpOffice\PhpSpreadsheet\Calculation\Exception;
use PhpOffice\PhpSpreadsheet\Cell\Cell as SpreadsheetCell;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** @mixin SpreadsheetCell */
class Cell
{
    use DelegatedMacroable;

    final public function __construct(
        private readonly SpreadsheetCell $cell,
    ) {
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function make(Worksheet $worksheet, string $coordinate): Cell
    {
        return new static($worksheet->getCell($coordinate));
    }

    public function getDelegate(): SpreadsheetCell
    {
        return $this->cell;
    }

    public function getValue(mixed $nullValue = null, bool $calculateFormulas = false, bool $formatData = true): mixed
    {
        $value = $nullValue;
        if ($this->cell->getValue() !== null) {
            if ($this->cell->getValue() instanceof RichText) {
                $value = $this->cell->getValue()->getPlainText();
            } elseif ($calculateFormulas) {
                try {
                    $value = $this->cell->getCalculatedValue();
                } catch (Exception) {
                    $value = $this->cell->getOldCalculatedValue();
                }
            } else {
                $value = $this->cell->getValue();
            }

            if ($formatData) {
                $style = $this->cell->getWorksheet()->getParent()->getCellXfByIndex($this->cell->getXfIndex());
                $value = NumberFormat::toFormattedString($value, $style->getNumberFormat()->getFormatCode());
            }
        }

        return app(Pipeline::class)->send($value)->through(config('excel.imports.cells.middleware', []))->thenReturn();
    }
}
