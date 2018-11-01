<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Validators\Failure;

interface SkipsOnFailure
{
    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures);
}
