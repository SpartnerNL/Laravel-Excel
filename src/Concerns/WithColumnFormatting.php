<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithColumnFormatting extends Export
{
    /**
     * @return array<string, string>
     */
    public function columnFormats(): array;
}
