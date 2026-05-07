<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Console\OutputStyle;

interface WithProgressBar
{
    public function getConsoleOutput(): OutputStyle;
}
