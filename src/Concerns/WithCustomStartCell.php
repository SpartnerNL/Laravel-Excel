<?php

namespace Maatwebsite\Excel\Concerns;

interface WithCustomStartCell
{
    /**
     * @return string
     */
    public function startCell(): string;
}
