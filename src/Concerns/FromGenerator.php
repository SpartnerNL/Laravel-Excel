<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Generator;

interface FromGenerator
{
    public function generator(): Generator;
}
