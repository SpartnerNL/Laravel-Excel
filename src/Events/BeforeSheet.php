<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Sheet;

class BeforeSheet extends Event
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var object
     */
    private $exportable;

    /**
     * @param  Sheet  $sheet
     * @param  object  $exportable
     */
    public function __construct(Sheet $sheet, $exportable)
    {
        $this->sheet       = $sheet;
        $this->exportable  = $exportable;
    }

    /**
     * @return Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
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
        return $this->sheet;
    }
}
