<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Console\OutputStyle;

interface WithProgressBar extends Import
{
    public function getConsoleOutput(): OutputStyle;
}
