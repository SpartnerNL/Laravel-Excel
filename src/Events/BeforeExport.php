<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Writer;

class BeforeExport extends Event
{
    public function __construct(
        public Writer $writer,
        object $exportable,
    ) {
        parent::__construct($exportable);
    }

    public function getWriter(): Writer
    {
        return $this->writer;
    }

    public function getDelegate(): mixed
    {
        return $this->writer;
    }
}
