<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithExportTemplate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/** @implements FromCollection<int, array{string, string}> */
class QueuedExportWithTemplate implements FromCollection, ShouldQueue, WithCustomStartCell, WithExportTemplate
{
    use Exportable;

    /**
     * @return Collection<int, array{string, string}>
     */
    public function collection(): Collection
    {
        return new Collection([
            ['Patrick', 'Brouwers'],
        ]);
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function exportTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'Queued users');

        return $spreadsheet;
    }
}
