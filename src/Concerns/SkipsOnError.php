<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Validators\Failure;
use Throwable;

interface SkipsOnError
{
    /**
     * @param Throwable $e
     */
    public function onError(Throwable $e);
}