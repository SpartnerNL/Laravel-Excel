<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Iterator;
use Traversable;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Row as RowInterface;
use Maatwebsite\Excel\Cell as CellInterface;
use PhpOffice\PhpSpreadsheet\Cell as PhpSpreadsheetCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as PhpSpreadsheetRow;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\CellIterator;

class Row implements RowInterface
{
    /**
     * @var PhpSpreadsheetRow
     */
    protected $row;

    /**
     * @var string
     */
    protected $startColumn = 'A';

    /**
     * @var string
     */
    protected $endColumn;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Sheet
     */
    protected $sheet;

    /**
     * @var array
     */
    protected $headings = [];

    /**
     * @param PhpSpreadsheetRow $row
     * @param array             $headings
     * @param Sheet             $sheet
     * @param Configuration     $configuration
     */
    public function __construct(PhpSpreadsheetRow $row, array $headings, Sheet $sheet, Configuration $configuration)
    {
        $this->row           = $row;
        $this->sheet         = $sheet;
        $this->configuration = $configuration;
        $this->headings      = $headings;
    }

    /**
     * @param string $column
     *
     * @return Row
     */
    public function setStartColumn(string $column)
    {
        $this->startColumn = $column;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Row
     */
    public function setEndColumn(string $column)
    {
        $this->endColumn = $column;

        return $this;
    }

    /**
     * @param string $heading
     *
     * @return CellInterface|null
     */
    public function get(string $heading)
    {
        if (!$this->has($heading)) {
            return null;
        }

        $columns = array_flip($this->headings);
        $column  = $columns[$heading];

        return $this->cell($column);
    }

    /**
     * @param string $heading
     *
     * @return bool
     */
    public function has(string $heading): bool
    {
        $columns = array_flip($this->headings);

        return isset($columns[$heading]);
    }

    /**
     * @param string $column
     *
     * @return CellInterface
     */
    public function cell(string $column): CellInterface
    {
        return $this->sheet->cell($column . $this->getRowNumber());
    }

    /**
     * @param string|null $startColumn
     * @param string|null $endColumn
     *
     * @return Iterator|CellInterface[]
     */
    public function cells(string $startColumn = 'A', string $endColumn = null)
    {
        return $this->getCellIterator($startColumn, $endColumn);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $index = 0;
        $cells = [];
        foreach ($this->getIterator() as $cell) {
            // Get heading, or use default array index
            $heading = isset($this->headings[$cell->getColumn()])
                ? $this->headings[$cell->getColumn()]
                : $index;

            $cells[$heading] = (string) $cell;

            $index++;
        }

        return $cells;
    }

    /**
     * @return int
     */
    public function getRowNumber(): int
    {
        return $this->row->getRowIndex();
    }

    /**
     * @return string
     */
    public function getHighestColumn(): string
    {
        return $this->row->getWorksheet()->getHighestColumn(
            $this->getRowNumber()
        );
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|CellIterator
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->getCellIterator($this->startColumn, $this->endColumn);
    }

    /**
     * @param string      $startColumn
     * @param string|null $endColumn
     *
     * @return Iterator
     */
    public function getCellIterator(string $startColumn = 'A', string $endColumn = null)
    {
        if ($endColumn === null) {
            $endColumn = $this->getHighestColumn();
        }

        return new CellIterator(
            $this->row->getCellIterator($startColumn, $endColumn),
            $this->configuration
        );
    }

    /**
     * Count elements of an object.
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        $highestColumn = $this->getHighestColumn();

        return $this->getColumnIndex($highestColumn);
    }

    /**
     * @return array
     */
    public function getHeadings(): array
    {
        return $this->headings;
    }

    /**
     * Whether a offset exists.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->get($offset)->setValue($value);
    }

    /**
     * Offset to unset.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->get($offset)->removeValue();
    }

    /**
     * @param string $column
     *
     * @return int
     */
    protected function getColumnIndex(string $column): int
    {
        return PhpSpreadsheetCell::columnIndexFromString($column);
    }
}
