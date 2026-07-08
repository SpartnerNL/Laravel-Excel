<?php

namespace Maatwebsite\Excel\Exceptions;

use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Validators\Failure;

class RowSkippedException extends Exception
{
    /**
     * @var Failure[]
     */
    private readonly array $failures;

    public function __construct(Failure ...$failures)
    {
        $this->failures = $failures;

        parent::__construct();
    }

    public function failures(): Collection
    {
        return new Collection($this->failures);
    }

    /**
     * @return int[]
     */
    public function skippedRows(): array
    {
        return $this->failures()->map->row()->all();
    }
}
