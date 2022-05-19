<?php

namespace Maatwebsite\Excel\Events;

class BeforeChunk
{
    /**
     * @var object
     */
    private $exportable;

    /**
     * @param object $exportable
     */
    public function __construct($exportable)
    {
        $this->exportable = $exportable;
    }


    /**
     * @return object
     */
    public function getConcernable()
    {
        return $this->exportable;
    }
}