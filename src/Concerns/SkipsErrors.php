<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;
use Throwable;

trait SkipsErrors
{
    /**
     * @var Throwable[]
     */
    protected $errors = [];

    public function onError(Throwable $e): void
    {
        $this->errors[] = $e;
    }

    /**
     * @return Throwable[]|Collection
     */
    public function errors(): Collection
    {
        return new Collection($this->errors);
    }
}
