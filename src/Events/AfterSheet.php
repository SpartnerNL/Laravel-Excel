<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Sheet;

class AfterSheet extends Event
{
    public function __construct(
        public Sheet $sheet,
        object $exportable,
    ) {
        parent::__construct($exportable);
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getDelegate(): mixed
    {
        return $this->sheet;
    }
}
