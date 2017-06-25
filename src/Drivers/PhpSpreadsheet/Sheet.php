<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Traversable;
use Maatwebsite\Excel\Configuration;
use PhpOffice\PhpSpreadsheet\Worksheet;
use Maatwebsite\Excel\Sheet as SheetInterface;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet\SheetHasRows;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet\SheetHasCells;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\RowIterator;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet\SheetHasColumns;

class Sheet implements SheetInterface
{
    use SheetHasCells,
        SheetHasRows,
        SheetHasColumns;

    /**
     * @var Worksheet
     */
    protected $worksheet;

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
     * @return array
     */
    public function toArray(): array
    {
        $rows = [];
        foreach ($this->getIterator() as $row) {
            $rows[] = $row->toArray();
        }

        return $rows;
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
        return intval($this->worksheet->getHighestRow());
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
        return $this->getRowIterator($this->startRow, $this->endRow);
    }

    /**
     * @return Worksheet
     */
    public function getWorksheet(): Worksheet
    {
        return $this->worksheet;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }
}
