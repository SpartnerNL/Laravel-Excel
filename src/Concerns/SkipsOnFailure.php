<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Validators\Failure;

interface SkipsOnFailure
{
    public function onFailure(Failure ...$failures);
}
