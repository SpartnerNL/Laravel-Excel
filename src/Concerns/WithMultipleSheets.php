<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithMultipleSheets extends Export, Import
{
    /**
     * @return array<int|string, Export|Import>
     */
    public function sheets(): array;
}
