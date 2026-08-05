<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QueuedExportWithCsvSettings implements Export, WithCustomCsvSettings, WithMultipleSheets
{
    use Exportable;

    /**
     * @return array<int, SheetWith100Rows>
     */
    public function sheets(): array
    {
        return [
            new SheetWith100Rows('Queued Sheet 1'),
        ];
    }

    /**
     * Root-level CSV settings that must survive a queued (multi-sheet) export.
     *
     * @return array<string, mixed>
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
        ];
    }
}
