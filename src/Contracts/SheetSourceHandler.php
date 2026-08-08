<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Contracts;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Sheet;

interface SheetSourceHandler
{
    public function canHandle(Export $sheetExport): bool;

    public function handle(Sheet $sheet, Export $sheetExport): void;
}
