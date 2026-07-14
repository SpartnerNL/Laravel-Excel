<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithEvents extends Export, Import
{
    /**
     * @return array<string, callable>
     */
    public function registerEvents(): array;
}
