<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Iterator;

interface FromIterator
{
    public function iterator(): Iterator;
}
