<?php

namespace Maatwebsite\Excel\Concerns;

interface WithColumnFormatting
{
    /**
     * @return array<string, string>
     */
    public function columnFormats(): array;
}
