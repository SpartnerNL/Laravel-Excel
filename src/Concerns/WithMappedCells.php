<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithMappedCells
{
    public function mapping(): array;
}
