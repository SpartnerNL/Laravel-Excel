<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;
use Throwable;

trait SkipsErrors
{
    /**
     * @var array<int, Throwable>
     */
    protected array $errors = [];

    public function onError(Throwable $e): void
    {
        $this->errors[] = $e;
    }

    /**
     * @return Collection<int, Throwable>
     */
    public function errors(): Collection
    {
        return new Collection($this->errors);
    }
}
