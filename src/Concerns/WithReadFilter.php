<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

interface WithReadFilter extends Import
{
    public function readFilter(): IReadFilter;
}
