<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Throwable;

interface SkipsOnError
{
    public function onError(Throwable $e): void;
}
