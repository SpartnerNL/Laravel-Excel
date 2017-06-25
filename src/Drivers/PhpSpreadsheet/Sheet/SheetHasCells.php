<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;

use Maatwebsite\Excel\Cell as CellInterface;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet;

trait SheetHasCells
{
    /**
     * @param string $coordinate
     *
     * @return bool
     */
    public function hasCell(string $coordinate): bool
    {
        return $this->getWorksheet()->cellExists($coordinate);
    }

    /**
     * @param string $coordinate
     * @param bool   $createIfNotExist
     *
     * @return CellInterface
     */
    public function cell(string $coordinate, bool $createIfNotExist = false): CellInterface
    {
        $cell = $this->getWorksheet()->getCell($coordinate, $createIfNotExist);

        return new Cell($cell, $this->configuration);
    }

    /**
     * @return Worksheet
     */
    abstract public function getWorksheet(): Worksheet;
}