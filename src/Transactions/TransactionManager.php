<?php

namespace Maatwebsite\Excel\Transactions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Manager;

class TransactionManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('excel.transactions.handler');
    }

    public function createNullDriver(): NullTransactionHandler
    {
        return new NullTransactionHandler;
    }

    public function createDbDriver(): DbTransactionHandler
    {
        return new DbTransactionHandler(
            DB::connection(config('excel.transactions.db.connection'))
        );
    }
}
