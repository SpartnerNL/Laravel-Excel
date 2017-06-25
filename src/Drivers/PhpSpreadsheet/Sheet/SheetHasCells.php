<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;

use PhpOffice\PhpSpreadsheet\Worksheet;
use Maatwebsite\Excel\Cell as CellInterface;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Cell;

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
     *
     * @return CellInterface
     */
    public function cell(string $coordinate): CellInterface
    {
        $cell = $this->getWorksheet()->getCell($coordinate);

        return new Cell($cell, $this->configuration);
    }

    /**
     * @return Worksheet
     */
    abstract public function getWorksheet(): Worksheet;
}
