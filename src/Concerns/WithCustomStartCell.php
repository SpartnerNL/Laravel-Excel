<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithCustomStartCell
{
    public function startCell(): string;
}
