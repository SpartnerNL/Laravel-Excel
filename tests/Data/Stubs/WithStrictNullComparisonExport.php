<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class WithStrictNullComparisonExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    use Exportable;

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect([
            ['0', 0, 0.0],
        ]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ['0', 0, 0.0];
    }
}
