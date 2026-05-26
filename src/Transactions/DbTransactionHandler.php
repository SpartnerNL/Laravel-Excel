<?php

namespace Maatwebsite\Excel\Transactions;

use Illuminate\Database\ConnectionInterface;

class DbTransactionHandler implements TransactionHandler
{
    public function __construct(
        private readonly ConnectionInterface $connection,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(callable $callback): mixed
    {
        return $this->connection->transaction($callback);
    }
}
