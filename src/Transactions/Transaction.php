<?php

namespace Maatwebsite\Excel\Transactions;

interface Transaction
{
    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function __invoke(callable $callback);
}