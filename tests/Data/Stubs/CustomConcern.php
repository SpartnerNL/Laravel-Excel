<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

interface CustomConcern
{
    /**
     * @return array<array-key, mixed>
     */
    public function custom();
}
