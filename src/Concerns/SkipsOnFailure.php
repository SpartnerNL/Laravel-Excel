<?php

namespace Seoperin\LaravelExcel\Concerns;

use Seoperin\LaravelExcel\Validators\Failure;

interface SkipsOnFailure
{
    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures);
}
