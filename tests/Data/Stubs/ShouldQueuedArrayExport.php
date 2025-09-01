<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ShouldQueuedArrayExport implements WithMultipleSheets, ShouldQueue
{
    use Exportable;

    /**
     * @return SheetWith100RowsFromArray[]
     */
    public function sheets(): array
    {
        return [
            new SheetWith100RowsFromArray('A'),
            new SheetWith100RowsFromArray('B'),
            new SheetWith100RowsFromArray('C'),
        ];
    }
}
