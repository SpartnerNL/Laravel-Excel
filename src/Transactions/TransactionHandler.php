<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Transactions;

interface TransactionHandler
{
    public function __invoke(callable $callback): mixed;
}
