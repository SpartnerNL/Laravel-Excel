<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\RowIterator;
use Maatwebsite\Excel\Sheet as SheetInterface;
use PhpOffice\PhpSpreadsheet\Worksheet;
use Traversable;

class Sheet implements SheetInterface, IteratorAggregate, Countable
{
    /**
     * @var Worksheet
     */
    private $worksheet;

    /**
     * @var int
     */
    protected $startRow = 1;

    /**
     * @var int|null
     */
    protected $endRow = null;

    /**
     * @param Worksheet $worksheet
     */
    public function __construct(Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
    }

    /**
     * @param int $rowNumber
     *
     * @return Sheet
     */
    public function setStartRow(int $rowNumber = 1)
    {
        $this->startRow = $rowNumber;

        return $this;
    }

    /**
     * @param int|null $rowNumber
     *
     * @return Sheet
     */
    public function setEndRow(int $rowNumber = null)
    {
        $this->endRow = $rowNumber;

        return $this;
    }

    /**
     * @param int $rowNumber
     *
     * @return Row
     */
    public function row(int $rowNumber): Row
    {
        $startRow = $rowNumber;
        $endRow = $rowNumber + 1;

        return $this->rows($startRow, $endRow)->first();
    }

    /**
     * @return Row
     */
    public function first(): Row
    {
        return $this->rows(1)->first();
    }

    /**
     * @param int|null $startRow
     * @param int|null $endRow
     *
     * @return RowIterator|Row[]
     */
    public function rows(int $startRow = null, int $endRow = null): RowIterator
    {
        if ($startRow !== null) {
            $this->setStartRow($startRow);
        }

        if ($endRow !== null) {
            $this->setEndRow($endRow);
        }

        return $this->getIterator();
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable|RowIterator
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new RowIterator(
            $this->worksheet->getRowIterator($this->startRow, $this->endRow)
        );
    }

    /**
     * Count elements of an object.
     *
     * @link  http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     *
     * @since 5.1.0
     */
    public function count()
    {
        return $this->worksheet->getHighestRow();
    }
}
