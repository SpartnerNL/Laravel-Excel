<?php

namespace Maatwebsite\Excel\Concerns;

use Throwable;

interface SkipsOnError
{
    public function onError(Throwable $e);
}
