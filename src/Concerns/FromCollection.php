<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Enumerable;

/**
 * @template TKey of array-key = array-key
 * @template TValue = mixed
 */
interface FromCollection extends Export
{
    /**
     * @return Enumerable<TKey, TValue>
     */
    public function collection(): Enumerable;
}
