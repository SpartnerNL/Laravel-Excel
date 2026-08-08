<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Handlers;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromScout;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Jobs\AppendPaginatedToSheet;
use Maatwebsite\Excel\Sheet;

class FromScoutHandler implements QueuedSheetSourceHandler, SheetSourceHandler
{
    public function canHandle(Export $sheetExport): bool
    {
        return $sheetExport instanceof FromScout;
    }

    public function handle(Sheet $sheet, Export $sheetExport): void
    {
        assert($sheetExport instanceof FromScout);

        $sheet->fromScout($sheetExport, $sheet->getDelegate());
    }

    public function buildJobs(Export $sheetExport, TemporaryFile $temporaryFile, string $writerType, int $sheetIndex, Export $export, int $chunkSize): iterable
    {
        assert($sheetExport instanceof FromScout);

        $page = $sheetExport->scout()->paginate($chunkSize);

        yield new AppendDataToSheet($sheetExport, $temporaryFile, $writerType, $sheetIndex, $page->items(), $export);

        for ($i = 2; $i <= $page->lastPage(); $i++) {
            yield new AppendPaginatedToSheet($sheetExport, $temporaryFile, $writerType, $sheetIndex, $i, $chunkSize, $export);
        }
    }
}
