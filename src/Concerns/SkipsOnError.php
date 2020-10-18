<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Model;
use Throwable;

interface SkipsOnError
{
    /**
     * @param Throwable $e
     */
    public function onError(Throwable $e, Model $model);
}
