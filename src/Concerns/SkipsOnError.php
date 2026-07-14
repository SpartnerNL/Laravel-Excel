<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Throwable;

interface SkipsOnError extends Import
{
    public function onError(Throwable $e): void;
}
