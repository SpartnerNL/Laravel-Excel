<?php

namespace Maatwebsite\Excel\Transactions;

interface TransactionHandler
{
    public function __invoke(callable $callback): mixed;
}
