<?php

namespace Maatwebsite\Excel;

use ArrayAccess;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as SpreadsheetRow;

class Row implements ArrayAccess
{
    use DelegatedMacroable;

    /**
     * @var array
     */
    protected $headingRow = [];

    /**
     * @var SpreadsheetRow
     */
    protected $row;

    /**
     * @var array|null
     */
    protected $rowCache;

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
     * @param null        $nullValue
     * @param bool        $calculateFormulas
     * @param bool        $formatData
     *
     * @param string|null $endColumn
     *
     * @return Collection
     */
    public function toCollection($nullValue = null, $calculateFormulas = false, $formatData = true, ?string $endColumn = null): Collection
    {
        return new Collection($this->toArray($nullValue, $calculateFormulas, $formatData, $endColumn));
    }

    /**
     * @param null        $nullValue
     * @param bool        $calculateFormulas
     * @param bool        $formatData
     * @param string|null $endColumn
     *
     * @return array
     */
    public function toArray($nullValue = null, $calculateFormulas = false, $formatData = true, ?string $endColumn = null)
    {
        if (is_array($this->rowCache)) {
            return $this->rowCache;
        }

        $cells = [];

        $i = 0;
        foreach ($this->row->getCellIterator('A', $endColumn) as $cell) {
            $value = (new Cell($cell))->getValue($nullValue, $calculateFormulas, $formatData);

            if (isset($this->headingRow[$i])) {
                $cells[$this->headingRow[$i]] = $value;
            } else {
                $cells[] = $value;
            }

            $i++;
        }

        $this->rowCache = $cells;

        return $cells;
    }

    /**
     * @return int
     */
    public function getIndex(): int
    {
        return $this->row->getRowIndex();
    }

    public function offsetExists($offset)
    {
        return isset(($this->toArray())[$offset]);
    }

    public function offsetGet($offset)
    {
        return ($this->toArray())[$offset];
    }

    public function offsetSet($offset, $value)
    {
        //
    }

    public function offsetUnset($offset)
    {
        //
    }
}
