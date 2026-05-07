<?php

namespace Maatwebsite\Excel\Transactions;

interface TransactionHandler
{
    /**
     * @return mixed
     */
    public function __invoke(callable $callback);
}
