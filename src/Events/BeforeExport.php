<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Writer;

class BeforeExport extends Event
{
    /**
     * @var Writer
     */
    public $writer;

    /**
     * @param  Writer  $writer
     * @param  object  $exportable
     */
    public function __construct(Writer $writer, $exportable)
    {
        $this->writer     = $writer;
        parent::__construct($exportable);
    }

    /**
     * @return Writer
     */
    public function getWriter(): Writer
    {
        return $this->writer;
    }

    /**
     * @return mixed
     */
    public function getDelegate()
    {
        return $this->writer;
    }
}
