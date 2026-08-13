<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Handlers;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Sheet;

class FromCollectionHandler implements QueuedSheetSourceHandler, SheetSourceHandler
{
    public function canHandle(Export $sheetExport): bool
    {
        return $sheetExport instanceof FromCollection;
    }

    public function handle(Sheet $sheet, Export $sheetExport): void
    {
        assert($sheetExport instanceof FromCollection);

        $sheet->fromCollection($sheetExport);
    }

    public function buildJobs(Export $sheetExport, TemporaryFile $temporaryFile, string $writerType, int $sheetIndex, Export $export, int $chunkSize): iterable
    {
        assert($sheetExport instanceof FromCollection);

        return $sheetExport
            ->collection()
            ->chunk($chunkSize)
            ->map(fn ($rows): AppendDataToSheet => new AppendDataToSheet(
                $sheetExport,
                $temporaryFile,
                $writerType,
                $sheetIndex,
                iterator_to_array($rows),
                $export,
            ));
    }
}
