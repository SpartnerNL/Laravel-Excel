<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

interface CustomSheetConcern
{
    /**
     * @return array<array-key, mixed>
     */
    public function custom();
}
