<?php

namespace Maatwebsite\Excel\Transactions;

class NullTransactionHandler implements TransactionHandler
{
    public function __invoke(callable $callback): mixed
    {
        return $callback();
    }
}
