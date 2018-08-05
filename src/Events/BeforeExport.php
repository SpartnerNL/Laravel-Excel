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
     * @var object
     */
    private $exportable;

    /**
     * @param Writer $writer
     * @param object $exportable
     */
    public function __construct(Writer $writer, $exportable)
    {
        $this->writer     = $writer;
        $this->exportable = $exportable;
    }

    /**
     * @return Writer
     */
    public function getWriter(): Writer
    {
        return $this->writer;
    }

    /**
     * @return object
     */
    public function getConcernable()
    {
        return $this->exportable;
    }

    /**
     * @return mixed
     */
    public function getDelegate()
    {
        return $this->writer;
    }
}
