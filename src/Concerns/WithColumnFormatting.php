<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithColumnFormatting
{
    public function columnFormats(): array;
}
