<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithColumnLimit extends Import
{
    public function endColumn(): string;
}
