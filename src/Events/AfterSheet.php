<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Sheet;

class AfterSheet extends Event
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param  Sheet  $sheet
     * @param  object  $exportable
     */
    public function __construct(Sheet $sheet, $exportable)
    {
        $this->sheet      = $sheet;
        parent::__construct($exportable);
    }

    /**
     * @return Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * @return mixed
     */
    public function getDelegate()
    {
        return $this->sheet;
    }
}
