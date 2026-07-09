<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Sheet;

class AfterChunk extends Event
{
    public function __construct(
        private readonly Sheet $sheet,
        object $importable,
        private readonly int $startRow,
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
