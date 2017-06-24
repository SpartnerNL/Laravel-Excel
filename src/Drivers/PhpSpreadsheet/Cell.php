<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Cell as CellInterface;
use PhpOffice\PhpSpreadsheet\Cell as PhpSpreadsheetCell;

class Cell implements CellInterface
{
    /**
     * @var PhpSpreadsheetCell
     */
    protected $cell;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param PhpSpreadsheetCell $cell
     * @param Configuration      $configuration
     */
    public function __construct(PhpSpreadsheetCell $cell, Configuration $configuration)
    {
        $this->cell          = $cell;
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getCoordinate(): string
    {
        return $this->cell->getCoordinate();
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->cell->getColumn();
    }

    /**
     * @return int
     */
    public function getRow(): int
    {
        return $this->cell->getRow();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->cell->getValue();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }
}
