<?php

namespace Maatwebsite\Excel\Concerns;

interface WithUpserts
{
    /**
     * @return string|array
     */
    public function uniqueBy();
}
