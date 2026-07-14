<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithLimit extends Import
{
    public function limit(): int;
}
