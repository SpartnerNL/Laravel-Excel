<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Reader;

class BeforeImport extends Event
{
    public function __construct(
        public Reader $reader,
        ?Import $importable,
    ) {
        parent::__construct($importable);
    }

    public function getReader(): Reader
    {
        return $this->reader;
    }

    public function getDelegate(): Reader
    {
        return $this->reader;
    }
}
