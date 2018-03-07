<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WithHeadingExport implements FromCollection, WithHeadings
{
    use Exportable;

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect([
           ['A1', 'B1' ,'C1'],
           ['A2', 'B2', 'C2']
        ]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ['A', 'B', 'C'];
    }
}