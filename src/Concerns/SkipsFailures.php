<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Validators\Failure;

trait SkipsFailures
{
    /**
     * @var array<int, Failure>
     */
    protected array $failures = [];

    public function onFailure(Failure ...$failures): void
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    /**
     * @return Collection<int, Failure>
     */
    public function failures(): Collection
    {
        return new Collection($this->failures);
    }
}
