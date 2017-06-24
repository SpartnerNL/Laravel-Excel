<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\SheetIterator;
use Maatwebsite\Excel\Exceptions\SheetNotFoundException;
use Maatwebsite\Excel\Reader as ReaderInterface;
use Maatwebsite\Excel\Sheet as SheetInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet;
use Traversable;

class Reader implements ReaderInterface, IteratorAggregate, Countable
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
     * @return ReaderInterface|Reader
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
     * @return Traversable|SheetInterface[]
     */
    public function sheets(): Traversable
    {
        $this->readFile();

        return $this->getIterator();
    }

    /**
     * @param string        $name
     * @param callable|null $callback
     *
     * @throws SheetNotFoundException
     * @return Sheet|SheetInterface
     */
    public function sheetByName(string $name, callable $callback = null): SheetInterface
    {
        $this->readFile();

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
     * @return Sheet
     */
    public function sheetByIndex(int $index, callable $callback = null): SheetInterface
    {
        $this->readFile();

        try {
            $sheet = $this->spreadsheet->getSheet($index);
        } catch (Exception $e) {
            throw new SheetNotFoundException($e->getMessage());
        }

        return $this->handleSheet(
            $sheet,
            $callback
        );
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *                     <b>Traversable</b>
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
        $this->readFile();

        return $this->spreadsheet->getSheetCount();
    }

    /**
     * Read the spreadsheet file.
     *
     * @return void
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
     * @return SheetInterface|Sheet
     */
    protected function handleSheet(Worksheet $worksheet, callable $callback = null): SheetInterface
    {
        $sheet = new Sheet($worksheet);

        if (is_callable($callback)) {
            $callback($sheet);
        }

        return $sheet;
    }
}
