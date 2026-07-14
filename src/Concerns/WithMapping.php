<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

/**
 * @template RowType of mixed
 */
interface WithMapping
{
    /**
     * @param  RowType  $row
     * @return array<mixed>
     */
    public function map(mixed $row): array;
}
