<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Imports\ModelManager;

class AfterBatch extends Event
{
    /**
     * @var ModelManager
     */
    public $manager;

    /**
     * @param  ModelManager  $manager
     * @param  object  $importable
     * @param  int  $batchSize
     * @param  int  $startRow
     */
    public function __construct(ModelManager $manager, $importable, private int $batchSize, private int $startRow)
    {
        $this->manager   = $manager;
        parent::__construct($importable);
    }

    public function getManager(): ModelManager
    {
        return $this->manager;
    }

    /**
     * @return mixed
     */
    public function getDelegate()
    {
        return $this->manager;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function getStartRow(): int
    {
        return $this->startRow;
    }
}
