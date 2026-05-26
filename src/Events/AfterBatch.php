<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Imports\ModelManager;

class AfterBatch extends Event
{
    public function __construct(
        public ModelManager $manager,
        object $importable,
        private readonly int $batchSize,
        private readonly int $startRow,
    ) {
        parent::__construct($importable);
    }

    public function getManager(): ModelManager
    {
        return $this->manager;
    }

    public function getDelegate(): mixed
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
