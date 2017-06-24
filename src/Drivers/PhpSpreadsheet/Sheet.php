<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Countable;
use Traversable;
use IteratorAggregate;
use Maatwebsite\Excel\Configuration;
use PhpOffice\PhpSpreadsheet\Worksheet;
use Maatwebsite\Excel\Row as RowInterface;
use Maatwebsite\Excel\Sheet as SheetInterface;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\RowIterator;

class Sheet implements SheetInterface, IteratorAggregate, Countable
{
    /**
     * @var Worksheet
     */
    protected $worksheet;

    /**
     * @var int
     */
    protected $startRow = 1;

    /**
     * @var int|null
     */
    protected $endRow = null;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Worksheet     $worksheet
     * @param Configuration $configuration
     */
    public function __construct(Worksheet $worksheet, Configuration $configuration)
    {
        $this->worksheet     = $worksheet;
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->worksheet->getTitle();
    }

    /**
     * @return int
     */
    public function getSheetIndex(): int
    {
        return $this->worksheet->getParent()->getIndex($this->worksheet);
    }

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
     * @param int $rowNumber
     *
     * @return RowInterface
     */
    public function row(int $rowNumber): RowInterface
    {
        $this->setStartRow($rowNumber);
        $this->setEndRow($rowNumber + 1);

        return $this->getIterator()->first();
    }

    /**
     * @return RowInterface
     */
    public function first(): RowInterface
    {
        return $this->row(1);
    }

    /**
     * @param int|null $startRow
     * @param int|null $endRow
     *
     * @return SheetInterface|RowInterface[]
     */
    public function rows(int $startRow = null, int $endRow = null): SheetInterface
    {
        if ($startRow !== null) {
            $this->setStartRow($startRow);
        }

        if ($endRow !== null) {
            $this->setEndRow($endRow);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $rows = [];
        foreach ($this->rows() as $row) {
            $rows[] = $row->toArray();
        }

        return $rows;
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|RowIterator
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new RowIterator(
            $this->worksheet->getRowIterator($this->startRow, $this->endRow),
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
        return $this->worksheet->getHighestRow();
    }
}
