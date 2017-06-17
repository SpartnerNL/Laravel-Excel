<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use IteratorAggregate;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\SheetIterator;
use Maatwebsite\Excel\Reader as ReaderInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet;
use Traversable;

class Reader implements ReaderInterface, IteratorAggregate
{
    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @param string        $filePath
     * @param callable|null $callback
     *
     * @return ReaderInterface
     */
    public function load(string $filePath, callable $callback = null): ReaderInterface
    {
        $this->filePath = $filePath;

        if (is_callable($callback)) {
            $callback($this);
        }

        return $this;
    }

    /**
     * @param string|int    $nameOrIndex
     * @param callable|null $callback
     *
     * @return Sheet
     */
    public function sheet($nameOrIndex, callable $callback = null): Sheet
    {
        if (is_int($nameOrIndex)) {
            return $this->sheetByIndex($nameOrIndex, $callback);
        }

        return $this->sheetByName($nameOrIndex, $callback);
    }

    /**
     * @return ReaderInterface
     */
    public function sheets(): ReaderInterface
    {
        $this->readFile();

        return $this;
    }

    /**
     * @param string        $name
     * @param callable|null $callback
     *
     * @return Sheet
     */
    public function sheetByName(string $name, callable $callback = null): Sheet
    {
        $this->readFile();

        $sheet = $this->spreadsheet->getSheetByName($name);

        return $this->handleSheet(
            $sheet,
            $callback
        );
    }

    /**
     * @param int           $index
     * @param callable|null $callback
     *
     * @return Sheet
     */
    public function sheetByIndex(int $index, callable $callback = null): Sheet
    {
        $this->readFile();

        $sheet = $this->spreadsheet->getSheet($index);

        return $this->handleSheet(
            $sheet,
            $callback
        );
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        $this->readFile();

        return new SheetIterator(
            $this->spreadsheet->getWorksheetIterator()
        );
    }

    /**
     * Read the spreadsheet file
     */
    protected function readFile()
    {
        if ($this->spreadsheet === null) {
            $this->spreadsheet = IOFactory::load($this->filePath);
        }
    }

    /**
     * @param Worksheet     $worksheet
     * @param callable|null $callback
     *
     * @return Sheet
     */
    protected function handleSheet(Worksheet $worksheet, callable $callback = null): Sheet
    {
        $sheet = new Sheet($worksheet);

        if (is_callable($callback)) {
            $callback($sheet);
        }

        return $sheet;
    }
}
