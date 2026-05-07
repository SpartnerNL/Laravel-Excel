<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Reader;

class AfterImport extends Event
{
    /**
     * @var Reader
     */
    public $reader;

    /**
     * @param  object  $importable
     */
    public function __construct(Reader $reader, $importable)
    {
        $this->reader = $reader;
        parent::__construct($importable);
    }

    public function getReader(): Reader
    {
        return $this->reader;
    }

    /**
     * @return mixed
     */
    public function getDelegate()
    {
        return $this->reader;
    }
}
