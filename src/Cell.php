<?php

namespace Maatwebsite\Excel;

use PhpOffice\PhpSpreadsheet\Calculation\Exception;
use PhpOffice\PhpSpreadsheet\Cell\Cell as SpreadsheetCell;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** @mixin SpreadsheetCell */
class Cell
{
    use DelegatedMacroable;

    /**
     * @var SpreadsheetCell
     */
    private $cell;

    /**
     * @param  SpreadsheetCell  $cell
     */
    public function __construct(SpreadsheetCell $cell)
    {
        $this->cell = $cell;
    }

    /**
     * @param  Worksheet  $worksheet
     * @param  string  $coordinate
     * @return Cell
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function make(Worksheet $worksheet, string $coordinate)
    {
        return new static($worksheet->getCell($coordinate));
    }

    /**
     * @return SpreadsheetCell
     */
    public function getDelegate(): SpreadsheetCell
    {
        return $this->cell;
    }

    /**
     * @param  null  $nullValue
     * @param  bool  $calculateFormulas
     * @param  bool  $formatData
     * @return mixed
     */
    public function getValue($nullValue = null, $calculateFormulas = false, $formatData = true)
    {
        $value = $nullValue;
        if ($this->cell->getValue() !== null) {
            if ($this->cell->getValue() instanceof RichText) {
                $value = $this->cell->getValue()->getPlainText();
            } elseif ($calculateFormulas) {
                try {
                    $value = $this->cell->getCalculatedValue();
                } catch (Exception $e) {
                    $value = $this->cell->getOldCalculatedValue();
                }
            } else {
                $value = $this->cell->getValue();
            }

            if ($formatData) {
                $style = $this->cell->getWorksheet()->getParent()->getCellXfByIndex($this->cell->getXfIndex());
                $value = NumberFormat::toFormattedString(
                    $value,
                    ($style && $style->getNumberFormat()) ? $style->getNumberFormat()->getFormatCode() : NumberFormat::FORMAT_GENERAL
                );
            }
        }

        return $value;
    }
}
