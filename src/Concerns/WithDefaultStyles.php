<?php

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Style\Style;

interface WithDefaultStyles
{
    public function defaultStyles(Style $defaultStyle): ?array;
}
