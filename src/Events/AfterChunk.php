<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Sheet;

class AfterChunk extends Event
{
    public function __construct(
        private Sheet $sheet,
        $importable,
        private int $startRow,
    ) {
        parent::__construct($importable);
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getDelegate(): mixed
    {
        return $this->sheet;
    }

    public function getStartRow(): int
    {
        return $this->startRow;
    }
}
