<?php

namespace Maatwebsite\Excel\Concerns;

interface WithMultipleSheets
{
    public function sheets(): array;
}
