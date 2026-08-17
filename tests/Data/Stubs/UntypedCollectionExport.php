<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Intentionally un-annotated: proves the concern template defaults hold.
 */
class UntypedCollectionExport implements FromCollection, WithMapping
{
    use Exportable;

    /**
     * @return Collection<int, array{string, string}>
     */
    public function collection(): Collection
    {
        return collect([
            ['A1', 'B1'],
            ['A2', 'B2'],
        ]);
    }

    public function map(mixed $row): array
    {
        return array_reverse((array) $row);
    }
}
