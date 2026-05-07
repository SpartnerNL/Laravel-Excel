<?php

namespace Maatwebsite\Excel\Transactions;

use Illuminate\Database\ConnectionInterface;

class DbTransactionHandler implements TransactionHandler
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    /**
     * @return mixed
     *
     * @throws \Throwable
     */
    public function __invoke(callable $callback)
    {
        return $this->connection->transaction($callback);
    }
}
