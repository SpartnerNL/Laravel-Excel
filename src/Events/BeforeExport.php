<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Writer;

class BeforeExport
{
    /**
     * @var Writer
     */
    public $writer;

    /**
     * @param Writer $writer
     */
    public function __construct(Writer $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @return Writer
     */
    public function getWriter(): Writer
    {
        return $this->writer;
    }
}
