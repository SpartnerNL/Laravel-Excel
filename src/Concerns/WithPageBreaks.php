<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithPageBreaks extends Export
{
    /**
     * Row numbers before which a horizontal page break is inserted.
     *
     * @return array<int>
     */
    public function pageBreaks(): array;
}
