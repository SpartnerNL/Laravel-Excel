<?php

namespace Maatwebsite\Excel\Concerns;

interface WithEvents
{
    /**
     * @return array<string, callable>
     */
    public function registerEvents(): array;
}
