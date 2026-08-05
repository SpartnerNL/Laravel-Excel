<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Contracts\View\View;

interface FromView extends Export
{
    public function view(): View;
}
