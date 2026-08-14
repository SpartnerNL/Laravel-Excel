<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithTabColor extends Export
{
    /**
     * RGB hex color for the sheet tab, without a leading #.
     * Example: 'FF0000' for red.
     */
    public function tabColor(): string;
}
