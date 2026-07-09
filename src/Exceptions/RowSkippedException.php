<?php

namespace Maatwebsite\Excel\Exceptions;

use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Validators\Failure;

class RowSkippedException extends Exception
{
    /**
     * @var array<int, Failure>
     */
    private readonly array $failures;

    public function __construct(Failure ...$failures)
    {
        $this->failures = array_values($failures);

        parent::__construct();
    }

    /**
     * @return Collection<int, Failure>
     */
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
