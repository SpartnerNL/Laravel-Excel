<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Columns\Column;

interface WithColumns
{
    /**
     * @return Column[]
     */
    public function columns(): array;
}
