<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Contracts;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Files\TemporaryFile;

interface QueuedSheetSourceHandler
{
    public function canHandle(Export $sheetExport): bool;

    /**
     * @return iterable<ShouldQueue>
     */
    public function buildJobs(
        Export $sheetExport,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex,
        Export $export,
        int $chunkSize,
    ): iterable;
}
