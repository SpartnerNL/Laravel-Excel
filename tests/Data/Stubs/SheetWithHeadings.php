<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetWithHeadings implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private string $title)
    {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return ['id', 'name'];
    }

    public function collection(): Collection
    {
        return collect();
    }
}
