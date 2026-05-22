<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Enumerable;

/**
 * @template TKey of array-key
 *
 * @template-covariant TValue
 */
interface FromCollection
{
    /**
     * @return Enumerable<TKey, TValue>
     */
    public function collection(): Enumerable;
}
