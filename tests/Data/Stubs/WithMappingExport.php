<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements WithMapping<list<string>>
 * @implements FromCollection<int, array{string, string, string}>
 */
class WithMappingExport implements FromCollection, WithMapping
{
    use Exportable;

    /**
     * @return Collection<int, array{string, string, string}>
     */
    public function collection(): Collection
    {
        return collect([
            ['A1', 'B1', 'C1'],
            ['A2', 'B2', 'C2'],
        ]);
    }

    public function map(mixed $row): array
    {
        return [
            'mapped-' . $row[0],
            'mapped-' . $row[1],
            'mapped-' . $row[2],
        ];
    }
}
