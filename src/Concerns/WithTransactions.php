<?php

namespace Maatwebsite\Excel\Concerns;

interface WithTransactions
{
    /**
     * @return int
     */
    public function useTransaction(): bool;
}
