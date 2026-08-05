<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Sheet;

class BeforeSheet extends Event
{
    public function __construct(
        public Sheet $sheet,
        Export|Import $concernable,
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
}
