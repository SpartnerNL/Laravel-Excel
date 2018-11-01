<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as SpreadsheetRow;

class Row
{
    use DelegatedMacroable;

    /**
     * @var array
     */
    protected $headingRow = [];

    /**
     * @var SpreadsheetRow
     */
    private $row;

    /**
     * @param SpreadsheetRow $row
     * @param array          $headingRow
     */
    public function __construct(SpreadsheetRow $row, array $headingRow = [])
    {
        $this->row        = $row;
        $this->headingRow = $headingRow;
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
     * @return Collection
     */
    public function toCollection($nullValue = null, $calculateFormulas = false, $formatData = true): Collection
    {
        return new Collection($this->toArray($nullValue, $calculateFormulas, $formatData));
    }

    /**
     * @param null $nullValue
     * @param bool $calculateFormulas
     * @param bool $formatData
     *
     * @return array
     */
    public function toArray($nullValue = null, $calculateFormulas = false, $formatData = true)
    {
        $cells = [];

        $i = 0;
        foreach ($this->row->getCellIterator() as $cell) {
            $value = (new Cell($cell))->getValue($nullValue, $calculateFormulas, $formatData);

            if (isset($this->headingRow[$i])) {
                $cells[$this->headingRow[$i]] = $value;
            } else {
                $cells[] = $value;
            }

            $i++;
        }

        return $cells;
    }

    /**
     * @return int
     */
    public function getIndex(): int
    {
        return $this->row->getRowIndex();
    }
}
