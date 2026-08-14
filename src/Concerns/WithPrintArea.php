<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithPrintArea extends Export
{
    /**
     * The cell range to set as the print area, e.g. 'A1:G100'.
     */
    public function printArea(): string;
}
