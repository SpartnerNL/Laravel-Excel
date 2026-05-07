<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Writer;

class BeforeWriting extends Event
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
     * @param  object  $exportable
     */
    public function __construct(Writer $writer, $exportable)
    {
        $this->writer = $writer;
        parent::__construct($exportable);
    }

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
