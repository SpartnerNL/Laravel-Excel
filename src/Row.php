<?php

namespace Maatwebsite\Excel;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as SpreadsheetRow;

class Row
{
    use DelegatedMacroable;

    /**
     * @var SpreadsheetRow
     */
    private $row;

    /**
     * @param SpreadsheetRow $row
     */
    public function __construct(SpreadsheetRow $row)
    {
        $this->row = $row;
    }

    /**
     * @return SpreadsheetRow
     */
    public function getDelegate(): SpreadsheetRow
    {
        return $this->row;
    }

    /**
     * @param null $nullValue
     * @param bool $calculateFormulas
     * @param bool $formatData
     *
     * @return array
     */
    public function toArray($nullValue = null, $calculateFormulas = true, $formatData = true)
    {
        $cells = [];

        /** @var Cell $cell */
        foreach ($this->row->getCellIterator() as $cell) {
            $value = $nullValue;
            if ($cell->getValue() !== null) {
                if ($cell->getValue() instanceof RichText) {
                    $value = $cell->getValue()->getPlainText();
                } else {
                    if ($calculateFormulas) {
                        $value = $cell->getCalculatedValue();
                    } else {
                        $value = $cell->getValue();
                    }
                }

                if ($formatData) {
                    $style = $this->row->getWorksheet()->getParent()->getCellXfByIndex($cell->getXfIndex());
                    $value = NumberFormat::toFormattedString(
                        $value,
                        ($style && $style->getNumberFormat()) ? $style->getNumberFormat()->getFormatCode() : NumberFormat::FORMAT_GENERAL
                    );
                }
            }

            $cells[] = $value;
        }

        return $cells;
    }
}