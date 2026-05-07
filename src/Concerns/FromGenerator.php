<?php

namespace Maatwebsite\Excel\Concerns;

use Generator;

interface FromGenerator
{
    public function generator(): Generator;
}
