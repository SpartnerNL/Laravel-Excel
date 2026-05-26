<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithLimit
{
    public function limit(): int;
}
