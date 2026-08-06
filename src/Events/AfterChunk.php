<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Sheet;

class AfterChunk extends Event
{
    public function __construct(
        private readonly Sheet $sheet,
        Export|Import $concernable,
        private readonly int $startRow,
    ) {
        parent::__construct($concernable);
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getDelegate(): Sheet
    {
        return $this->sheet;
    }

    public function getStartRow(): int
    {
        return $this->startRow;
    }
}
