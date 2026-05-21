<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Reader;

class BeforeImport extends Event
{
    public function __construct(
        public Reader $reader,
        ?object $importable,
    ) {
        parent::__construct($importable);
    }

    public function getReader(): Reader
    {
        return $this->reader;
    }

    public function getDelegate(): mixed
    {
        return $this->reader;
    }
}
