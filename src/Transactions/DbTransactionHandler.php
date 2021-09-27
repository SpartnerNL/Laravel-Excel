<?php

namespace Maatwebsite\Excel\Transactions;

use Illuminate\Database\ConnectionInterface;

class DbTransactionHandler implements TransactionHandler
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @param  ConnectionInterface  $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param  callable  $callback
     * @return mixed
     *
     * @throws \Throwable
     */
    public function __invoke(callable $callback)
    {
        return $this->connection->transaction($callback);
    }
}
