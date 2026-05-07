<?php

namespace Maatwebsite\Excel\Transactions;

class NullTransactionHandler implements TransactionHandler
{
    /**
     * @return mixed
     */
    public function __invoke(callable $callback)
    {
        return $callback();
    }
}
