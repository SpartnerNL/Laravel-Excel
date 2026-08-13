<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Sheet;

class DummySourceHandler implements QueuedSheetSourceHandler, SheetSourceHandler
{
    public function canHandle(Export $sheetExport): bool
    {
        return $sheetExport instanceof FromDummySource;
    }

    public function handle(Sheet $sheet, Export $sheetExport): void
    {
        assert($sheetExport instanceof FromDummySource);

        $sheet->appendRows($sheetExport->data(), $sheetExport);
    }

    public function buildJobs(
        Export $sheetExport,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex,
        Export $export,
        int $chunkSize,
    ): iterable {
        assert($sheetExport instanceof FromDummySource);

        foreach (array_chunk($sheetExport->data(), $chunkSize) as $chunk) {
            yield new AppendDataToSheet($sheetExport, $temporaryFile, $writerType, $sheetIndex, $chunk, $export);
        }
    }
}
