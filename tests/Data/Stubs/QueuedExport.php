<?php

namespace Seoperin\LaravelExcel\Tests\Data\Stubs;

use Seoperin\LaravelExcel\Concerns\Exportable;
use Seoperin\LaravelExcel\Concerns\WithMultipleSheets;

class QueuedExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @return SheetWith100Rows[]
     */
    public function sheets(): array
    {
        return [
            new SheetWith100Rows('A'),
            new SheetWith100Rows('B'),
            new SheetWith100Rows('C'),
        ];
    }
}
