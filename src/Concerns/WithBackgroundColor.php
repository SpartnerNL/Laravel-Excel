<?php

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Style\Color;

interface WithBackgroundColor
{
    /**
     * @return string|array<string, mixed>|Color
     */
    public function backgroundColor(): string|array|Color;
}
