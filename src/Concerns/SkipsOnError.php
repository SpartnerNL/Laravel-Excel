<?php

namespace Maatwebsite\Excel\Concerns;

use Throwable;

interface SkipsOnError
{
    /**
     * @param Throwable $e
     */
    public function onError(Throwable $e);
}
