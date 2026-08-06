<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Imports\ModelManager;

class AfterBatch extends Event
{
    public function __construct(
        public ModelManager $manager,
        Import $importable,
        private readonly int $batchSize,
        private readonly int $startRow,
    ) {
        parent::__construct($importable);
    }

    public function getManager(): ModelManager
    {
        return $this->manager;
    }

    public function getDelegate(): ModelManager
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
