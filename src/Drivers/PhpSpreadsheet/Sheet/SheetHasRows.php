<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;

use IteratorAggregate;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Row;
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
     * @var array
     */
    protected $headings = [];

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
     * @param int|false $headingRow
     *
     * @return SheetInterface
     */
    public function useRowAsHeading($headingRow): SheetInterface
    {
        $this->getConfiguration()->getReaderConfiguration()->setHeadingRow($headingRow);

        return $this;
    }

    /**
     * @return SheetInterface
     */
    public function useFirstRowAsHeading(): SheetInterface
    {
        return $this->useRowAsHeading(1);
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
        $iterator = $this->getWorksheet()->getRowIterator($rowNumber, $rowNumber + 1);

        return new Row(
            $iterator->current(),
            $this->getHeadings(),
            $this,
            $this->getConfiguration()
        );
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
        $headings = $this->getHeadings();

        $readerConfig = $this->getConfiguration()->getReaderConfiguration();

        // Move start position to the row after the heading row
        if ($readerConfig->hasHeadingRow() && $startRow <= $readerConfig->getHeadingRow()) {
            $startRow = $readerConfig->getHeadingRow() + 1;
        }

        // TODO: add interface
        return new RowIterator(
            $this,
            $headings,
            $this->getWorksheet()->getRowIterator($startRow, $endRow),
            $this->configuration
        );
    }

    /**
     * @return array
     */
    public function getHeadings(): array
    {
        // Return cached headings
        if (count($this->headings) > 0) {
            return $this->headings;
        }

        $readerConfiguration = $this->getConfiguration()->getReaderConfiguration();

        // Build a default heading based on column key, if heading row is disabled
        if (!$readerConfiguration->hasHeadingRow()) {
            return [];
        }

        $headingRow = $readerConfiguration->getHeadingRow();

        $iterator = $this->getWorksheet()->getRowIterator(
            $headingRow,
            $headingRow + 1
        );

        foreach ($iterator->current()->getCellIterator() as $column => $cell) {
            $this->headings[$column] = $cell->getValue();
        }

        return $this->headings;
    }

    /**
     * @return Worksheet
     */
    abstract public function getWorksheet(): Worksheet;

    /**
     * @return Configuration
     */
    abstract public function getConfiguration(): Configuration;
}
