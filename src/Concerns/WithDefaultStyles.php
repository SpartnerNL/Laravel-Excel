<?php

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Style\Style;

interface WithDefaultStyles
{
    /**
     * @return array<string, mixed>|null
     */
    public function defaultStyles(Style $defaultStyle): ?array;
}
