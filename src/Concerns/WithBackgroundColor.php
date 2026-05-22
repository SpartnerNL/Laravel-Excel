<?php

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Style\Color;

interface WithBackgroundColor
{
    public function backgroundColor(): string|array|Color;
}
