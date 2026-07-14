<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithCustomStartCell extends Export
{
    public function startCell(): string;
}
