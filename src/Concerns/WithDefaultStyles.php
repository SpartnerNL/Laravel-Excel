<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Style\Style;

interface WithDefaultStyles extends Export
{
    /**
     * @return array<string, mixed>|null
     */
    public function defaultStyles(Style $defaultStyle): ?array;
}
