<?php

namespace Maatwebsite\Excel\Concerns;

interface WithEvents
{
    /**
     * @return array
     */
    public function registerEvents(): array;
}
