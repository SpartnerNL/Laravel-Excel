<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QueuedMultiSheetExport implements ShouldQueue, WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        return [
            new SheetWithHeadings('First sheet'),
            new SheetWithHeadings('Second sheet'),
        ];
    }
}
