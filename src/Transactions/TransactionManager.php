<?php

namespace Maatwebsite\Excel\Transactions;

use Illuminate\Support\Manager;

class TransactionManager extends Manager
{
    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return config('excel.transactions.driver');
    }

    /**
     * @return NullTransaction
     */
    public function createNullDriver()
    {
        return new NullTransaction();
    }

    /**
     * @return DbTransaction
     */
    public function createDbDriver()
    {
        return new DbTransaction(
            $this->app->get('db.connection')
        );
    }
}
