<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithColumnLimit
{
    public function endColumn(): string;
}
