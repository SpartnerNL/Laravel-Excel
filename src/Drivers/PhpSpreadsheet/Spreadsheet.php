<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Iterator;
use Traversable;
use IteratorAggregate;
use Maatwebsite\Excel\Configuration;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet;
use Maatwebsite\Excel\Sheet as SheetInterface;
use Maatwebsite\Excel\Exceptions\SheetNotFoundException;
use Maatwebsite\Excel\Spreadsheet as SpreadsheetInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet as PhpSpreadsheet;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\SheetIterator;

class Spreadsheet implements SpreadsheetInterface
{
    /**
     * @var PhpSpreadsheet
     */
    protected $spreadsheet;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param PhpSpreadsheet $spreadsheet
     * @param Configuration  $configuration
     */
    public function __construct(PhpSpreadsheet $spreadsheet, Configuration $configuration)
    {
        $this->spreadsheet   = $spreadsheet;
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return (string) $this->spreadsheet->getProperties()->getTitle();
    }

    /**
     * @return Iterator|SheetInterface[]
     */
    public function sheets()
    {
        return $this->getSheetIterator();
    }

    /**
     * @param string|int    $nameOrIndex
     * @param callable|null $callback
     *
     * @return SheetInterface
     */
    public function sheet($nameOrIndex, callable $callback = null): SheetInterface
    {
        if (is_int($nameOrIndex)) {
            return $this->sheetByIndex($nameOrIndex, $callback);
        }

        return $this->sheetByName($nameOrIndex, $callback);
    }

    /**
     * @param string        $name
     * @param callable|null $callback
     *
     * @throws SheetNotFoundException
     * @return SheetInterface
     */
    public function sheetByName(string $name, callable $callback = null): SheetInterface
    {
        $sheet = $this->spreadsheet->getSheetByName($name);

        if ($sheet === null) {
            throw SheetNotFoundException::byName($name);
        }

        return $this->handleSheet(
            $sheet,
            $callback
        );
    }

    /**
     * @param int           $index
     * @param callable|null $callback
     *
     * @throws SheetNotFoundException
     * @return SheetInterface
     */
    public function sheetByIndex(int $index, callable $callback = null): SheetInterface
    {
        try {
            $sheet = $this->spreadsheet->getSheet($index);
        } catch (Exception $e) {
            throw SheetNotFoundException::fromException($e);
        }

        return $this->handleSheet(
            $sheet,
            $callback
        );
    }

    /**
     * @return SheetInterface
     */
    public function first(): SheetInterface
    {
        return $this->sheetByIndex(0);
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|SheetIterator
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->getSheetIterator();
    }

    /**
     * @return IteratorAggregate|SheetIterator|SheetInterface[]
     */
    public function getSheetIterator()
    {
        return new SheetIterator(
            $this->spreadsheet->getWorksheetIterator(),
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
        return $this->spreadsheet->getSheetCount();
    }

    /**
     * @param Worksheet     $worksheet
     * @param callable|null $callback
     *
     * @return Sheet
     */
    protected function handleSheet(Worksheet $worksheet, callable $callback = null): Sheet
    {
        $sheet = new Sheet($worksheet, $this->configuration);

        if (is_callable($callback)) {
            $callback($sheet);
        }

        return $sheet;
    }
}
