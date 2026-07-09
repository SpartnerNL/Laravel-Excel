<?php

namespace Maatwebsite\Excel\Concerns;

interface WithProperties
{
    /**
     * @return array<string, mixed>
     */
    public function properties(): array;
}
