<?php

namespace Maatwebsite\Excel\Concerns;

use Iterator;

interface FromIterator
{
    public function iterator(): Iterator;
}
