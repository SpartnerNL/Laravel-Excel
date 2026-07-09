<?php

namespace Maatwebsite\Excel\Concerns;

interface FromArray
{
    /**
     * @return array<int, array<array-key, mixed>>
     */
    public function array(): array;
}
