<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Export;

interface FromDummySource extends Export
{
    /** @return array<int, array<int, mixed>> */
    public function data(): array;
}
