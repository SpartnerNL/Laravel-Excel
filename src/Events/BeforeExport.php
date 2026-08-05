<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Writer;

class BeforeExport extends Event
{
    public function __construct(
        public Writer $writer,
        Export $exportable,
    ) {
        parent::__construct($exportable);
    }

    public function getWriter(): Writer
    {
        return $this->writer;
    }

    public function getDelegate(): Writer
    {
        return $this->writer;
    }
}
