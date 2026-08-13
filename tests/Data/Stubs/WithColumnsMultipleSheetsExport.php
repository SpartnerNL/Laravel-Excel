<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WithColumnsMultipleSheetsExport implements Export, WithMultipleSheets
{
    use Exportable;

    /**
     * @return FromQuery[]
     */
    public function sheets(): array
    {
        return [
            new FromUsersQueryExportWithColumns,
            new FromUsersEmailExportWithColumns,
        ];
    }
}
