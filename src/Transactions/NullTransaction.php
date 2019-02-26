<?php

namespace Maatwebsite\Excel\Transactions;

class NullTransaction implements Transaction
{
    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function __invoke(callable $callback)
    {
        return $callback();
    }
}
