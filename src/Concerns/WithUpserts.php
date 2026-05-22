<?php

namespace Maatwebsite\Excel\Concerns;

interface WithUpserts
{
    public function uniqueBy(): string|array;
}
