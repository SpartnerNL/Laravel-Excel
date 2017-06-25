<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;

use IteratorAggregate;
use PhpOffice\PhpSpreadsheet\Worksheet;
use Maatwebsite\Excel\Row as RowInterface;
use Maatwebsite\Excel\Sheet as SheetInterface;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\RowIterator;

trait SheetHasRows
{
    /**
     * @var int
     */
    protected $startRow = 1;

    /**
     * @var int|null
     */
    protected $endRow = null;

    /**
     * @param int $rowNumber
     *
     * @return SheetInterface
     */
    public function setStartRow(int $rowNumber = 1): SheetInterface
    {
        $this->startRow = $rowNumber;

        return $this;
    }

    /**
     * @param int|null $rowNumber
     *
     * @return SheetInterface
     */
    public function setEndRow(int $rowNumber = null): SheetInterface
    {
        $this->endRow = $rowNumber;

        return $this;
    }

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return \Traversable|RowIterator|RowInterface[]
     */
    public function rows(int $startRow = 1, int $endRow = null)
    {
        return $this->getRowIterator($startRow, $endRow);
    }

    /**
     * @param int $rowNumber
     *
     * @return RowInterface
     */
    public function row(int $rowNumber): RowInterface
    {
        return $this->getRowIterator($rowNumber, $rowNumber + 1)->first();
    }

    /**
     * @return RowInterface
     */
    public function first(): RowInterface
    {
        return $this->row(1);
    }

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return IteratorAggregate|RowIterator
     */
    public function getRowIterator(int $startRow = 1, int $endRow = null)
    {
        // TODO: add interface
        return new RowIterator(
            $this,
            $this->getWorksheet()->getRowIterator($startRow, $endRow),
            $this->configuration
        );
    }

    /**
     * @return Worksheet
     */
    abstract public function getWorksheet(): Worksheet;
}
