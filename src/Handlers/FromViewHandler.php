<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Handlers;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\AppendViewToSheet;
use Maatwebsite\Excel\Sheet;

class FromViewHandler implements QueuedSheetSourceHandler, SheetSourceHandler
{
    public function canHandle(Export $sheetExport): bool
    {
        return $sheetExport instanceof FromView;
    }

    public function handle(Sheet $sheet, Export $sheetExport): void
    {
        assert($sheetExport instanceof FromView);

        $sheet->fromView($sheetExport);
    }

    public function buildJobs(Export $sheetExport, TemporaryFile $temporaryFile, string $writerType, int $sheetIndex, Export $export, int $chunkSize): iterable
    {
        assert($sheetExport instanceof FromView);

        yield new AppendViewToSheet($sheetExport, $temporaryFile, $writerType, $sheetIndex, $export);
    }
}
