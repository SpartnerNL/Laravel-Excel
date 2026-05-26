<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithEvents
{
    public function registerEvents(): array;
}
