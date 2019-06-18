<?php

namespace Seoperin\LaravelExcel\Concerns;

use Illuminate\Support\Collection;
use Seoperin\LaravelExcel\Validators\Failure;

trait SkipsFailures
{
    /**
     * @var Failure[]
     */
    protected $failures = [];

    /**
     * @param Failure ...$failures
     */
    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    /**
     * @return Failure[]|Collection
     */
    public function failures(): Collection
    {
        return new Collection($this->failures);
    }
}
