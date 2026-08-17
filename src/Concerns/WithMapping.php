<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

/**
 * @template-contravariant RowType = mixed
 */
interface WithMapping
{
    /**
     * @param  RowType  $row
     * @return array<array-key, mixed>
     */
    public function map(mixed $row): array;
}
