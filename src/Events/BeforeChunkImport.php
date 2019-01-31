<?php

namespace Maatwebsite\Excel\Events;

use PhpOffice\PhpSpreadsheet\Reader\IReader;

class BeforeChunkImport extends Event
{
    /**
     * @var IReader
     */
    public $reader;

    /**
     * @var object
     */
    private $importable;

    /**
     * @param IReader $reader
     * @param object $importable
     */
    public function __construct(IReader $reader, $importable)
    {
        $this->reader     = $reader;
        $this->importable = $importable;
    }

    /**
     * @return IReader
     */
    public function getReader(): IReader
    {
        return $this->reader;
    }

    /**
     * @return object
     */
    public function getConcernable()
    {
        return $this->importable;
    }

    /**
     * @return mixed
     */
    public function getDelegate()
    {
        return $this->reader;
    }
}
