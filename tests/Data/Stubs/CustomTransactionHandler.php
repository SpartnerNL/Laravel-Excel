<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Transactions\TransactionHandler;

class CustomTransactionHandler implements TransactionHandler
{
    public function __invoke(callable $callback)
    {
        return $callback();
    }
}
