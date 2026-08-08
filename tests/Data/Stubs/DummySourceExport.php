<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Exportable;

class DummySourceExport implements FromDummySource
{
    use Exportable;

    /** @return array<int, array<int, mixed>> */
    public function data(): array
    {
        return [
            ['Name', 'Age'],
            ['Alice', 30],
            ['Bob', 25],
            ['Carol', 35],
        ];
    }
}
