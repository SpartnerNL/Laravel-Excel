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

    /**
     * @param  Throwable  $e
     */
    public function onError(Throwable $e)
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
