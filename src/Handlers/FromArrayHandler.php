<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Handlers;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Sheet;

class FromArrayHandler implements SheetSourceHandler
{
    public function canHandle(Export $sheetExport): bool
    {
        return $sheetExport instanceof FromArray;
    }

    public function handle(Sheet $sheet, Export $sheetExport): void
    {
        assert($sheetExport instanceof FromArray);

        $sheet->fromArray($sheetExport);
    }
}
