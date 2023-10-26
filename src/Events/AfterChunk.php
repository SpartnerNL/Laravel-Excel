<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Sheet;

class AfterChunk extends Event
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var int
     */
    private $startRow;

    public function __construct(Sheet $sheet, $importable, int $startRow)
    {
        $this->sheet     = $sheet;
        $this->startRow  = $startRow;
        parent::__construct($importable);
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getDelegate()
    {
        return $this->sheet;
    }

    public function getStartRow(): int
    {
        return $this->startRow;
    }
}
