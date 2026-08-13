<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Handlers;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomQuerySize;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\AppendQueryToSheet;
use Maatwebsite\Excel\Sheet;

class FromQueryHandler implements QueuedSheetSourceHandler, SheetSourceHandler
{
    public function canHandle(Export $sheetExport): bool
    {
        return $sheetExport instanceof FromQuery;
    }

    public function handle(Sheet $sheet, Export $sheetExport): void
    {
        assert($sheetExport instanceof FromQuery);

        $sheet->fromQuery($sheetExport, $sheet->getDelegate());
    }

    public function buildJobs(Export $sheetExport, TemporaryFile $temporaryFile, string $writerType, int $sheetIndex, Export $export, int $chunkSize): iterable
    {
        assert($sheetExport instanceof FromQuery);

        $query = $sheetExport->query();
        $count = $sheetExport instanceof WithCustomQuerySize ? $sheetExport->querySize() : $query->count();
        $spins = (int) ceil($count / $chunkSize);

        for ($page = 1; $page <= $spins; $page++) {
            yield new AppendQueryToSheet($sheetExport, $temporaryFile, $writerType, $sheetIndex, $page, $chunkSize, $export);
        }
    }
}
