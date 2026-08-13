<?php

namespace Maatwebsite\Excel;

use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\ShouldBatch;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFileFactory;
use Maatwebsite\Excel\Jobs\CloseSheet;
use Maatwebsite\Excel\Jobs\QueueExport;
use Maatwebsite\Excel\Jobs\StoreQueuedExport;

class QueuedWriter
{
    protected int $chunkSize;

    public function __construct(
        protected Writer $writer,
        protected TemporaryFileFactory $temporaryFileFactory,
        protected HandlerRegistry $handlerRegistry,
    ) {
        $this->chunkSize = config('excel.exports.chunk_size', 1000);
    }

    /**
     * @param  array<string, mixed>|string  $diskOptions
     */
    public function store(Export $export, string $filePath, ?string $disk = null, ?string $writerType = null, array|string $diskOptions = []): PendingDispatch|PendingBatch
    {
        $extension     = pathinfo($filePath, PATHINFO_EXTENSION);
        $temporaryFile = $this->temporaryFileFactory->make($extension);

        $jobs = $this->buildExportJobs($export, $temporaryFile, $writerType);

        $queueExportJob = new QueueExport($export, $temporaryFile, $writerType);

        $jobs->push(new StoreQueuedExport(
            $temporaryFile,
            $filePath,
            $disk,
            $diskOptions
        ));

        // Check if the export class is batchable
        if ($export instanceof ShouldBatch) {
            return Bus::batch([
                $jobs->prepend($queueExportJob)
                    ->toArray(),
            ]);
        }

        return new PendingDispatch(
            $queueExportJob->chain($jobs->toArray())
        );
    }

    /**
     * @return Collection<int, object>
     */
    private function buildExportJobs(Export $export, TemporaryFile $temporaryFile, string $writerType): Collection
    {
        $sheetExports = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheetExports = $export->sheets();
        }

        $jobs = new Collection;
        foreach ($sheetExports as $sheetIndex => $sheetExport) {
            $handler = $this->handlerRegistry->findQueuedHandler($sheetExport);

            if ($handler instanceof QueuedSheetSourceHandler) {
                foreach ($handler->buildJobs(
                    $sheetExport,
                    $temporaryFile,
                    $writerType,
                    $sheetIndex,
                    $export,
                    $this->getChunkSize($sheetExport),
                ) as $job) {
                    $jobs->push($job);
                }
            }

            $jobs->push(new CloseSheet($sheetExport, $temporaryFile, $writerType, $sheetIndex, $export));
        }

        return $jobs;
    }

    private function getChunkSize(Export $export): int
    {
        if ($export instanceof WithCustomChunkSize) {
            return $export->chunkSize();
        }

        return $this->chunkSize;
    }
}
