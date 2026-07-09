<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Contracts\View\View;

interface FromView
{
    public function view(): View;
}
