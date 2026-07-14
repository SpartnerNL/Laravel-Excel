<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithProperties extends Export
{
    /**
     * @return array<string, mixed>
     */
    public function properties(): array;
}
