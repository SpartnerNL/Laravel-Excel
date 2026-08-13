<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Handlers;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Sheet;

class FromGeneratorHandler implements SheetSourceHandler
{
    public function canHandle(Export $sheetExport): bool
    {
        return $sheetExport instanceof FromGenerator;
    }

    public function handle(Sheet $sheet, Export $sheetExport): void
    {
        assert($sheetExport instanceof FromGenerator);

        $sheet->fromGenerator($sheetExport);
    }
}
