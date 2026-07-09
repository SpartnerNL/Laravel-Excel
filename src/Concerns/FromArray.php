<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface FromArray
{
    /**
     * @return array<int, array<array-key, mixed>>
     */
    public function array(): array;
}
