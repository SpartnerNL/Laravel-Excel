<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Validators\Failure;

interface SkipsOnFailure
{
    public function onFailure(Failure ...$failures): void;
}
