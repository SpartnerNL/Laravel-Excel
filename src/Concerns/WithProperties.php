<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithProperties
{
    /**
     * @return array<string, mixed>
     */
    public function properties(): array;
}
