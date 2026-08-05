<?php

namespace Maatwebsite\Excel;

use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromScout;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldBatch;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithCustomQuerySize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFileFactory;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Jobs\AppendPaginatedToSheet;
use Maatwebsite\Excel\Jobs\AppendQueryToSheet;
use Maatwebsite\Excel\Jobs\AppendViewToSheet;
use Maatwebsite\Excel\Jobs\CloseSheet;
use Maatwebsite\Excel\Jobs\QueueExport;
use Maatwebsite\Excel\Jobs\StoreQueuedExport;

class QueuedWriter
{
    protected int $chunkSize;

    public function __construct(
        protected Writer $writer,
        protected TemporaryFileFactory $temporaryFileFactory,
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
            if ($sheetExport instanceof FromCollection) {
                $jobs = $jobs->merge($this->exportCollection($sheetExport, $temporaryFile, $writerType, $sheetIndex, $export));
            } elseif ($sheetExport instanceof FromQuery) {
                $jobs = $jobs->merge($this->exportQuery($sheetExport, $temporaryFile, $writerType, $sheetIndex, $export));
            } elseif ($sheetExport instanceof FromScout) {
                $jobs = $jobs->merge($this->exportScout($sheetExport, $temporaryFile, $writerType, $sheetIndex, $export));
            } elseif ($sheetExport instanceof FromView) {
                $jobs = $jobs->merge($this->exportView($sheetExport, $temporaryFile, $writerType, $sheetIndex, $export));
            }

            $jobs->push(new CloseSheet($sheetExport, $temporaryFile, $writerType, $sheetIndex, $export));
        }

        return $jobs;
    }

    /**
     * @param  FromCollection<array-key, mixed>  $sheetExport
     * @return Enumerable<int, AppendDataToSheet>
     */
    private function exportCollection(
        FromCollection $sheetExport,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex,
        object $export
    ): Enumerable {
        return $sheetExport
            ->collection()
            ->chunk($this->getChunkSize($sheetExport))
            ->map(function ($rows) use ($writerType, $temporaryFile, $sheetIndex, $sheetExport, $export): AppendDataToSheet {
                $rows = iterator_to_array($rows);

                return new AppendDataToSheet(
                    $sheetExport,
                    $temporaryFile,
                    $writerType,
                    $sheetIndex,
                    $rows,
                    $export
                );
            });
    }

    /**
     * @return Collection<int, object>
     */
    private function exportQuery(
        FromQuery $sheetExport,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex,
        object $export
    ): Collection {
        $query = $sheetExport->query();
        $count = $sheetExport instanceof WithCustomQuerySize ? $sheetExport->querySize() : $query->count();
        $spins = ceil($count / $this->getChunkSize($sheetExport));

        $jobs = new Collection;

        for ($page = 1; $page <= $spins; $page++) {
            $jobs->push(new AppendQueryToSheet(
                $sheetExport,
                $temporaryFile,
                $writerType,
                $sheetIndex,
                $page,
                $this->getChunkSize($sheetExport),
                $export
            ));
        }

        return $jobs;
    }

    /**
     * @return Collection<int, object>
     */
    private function exportScout(
        FromScout $sheetExport,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex,
        object $export
    ): Collection {
        $jobs = new Collection;

        $chunk = $sheetExport->scout()->paginate($this->getChunkSize($sheetExport));
        // Append first page
        $jobs->push(new AppendDataToSheet(
            $sheetExport,
            $temporaryFile,
            $writerType,
            $sheetIndex,
            $chunk->items(),
            $export
        ));

        // Append rest of pages
        for ($page = 2; $page <= $chunk->lastPage(); $page++) {
            $jobs->push(new AppendPaginatedToSheet(
                $sheetExport,
                $temporaryFile,
                $writerType,
                $sheetIndex,
                $page,
                $this->getChunkSize($sheetExport),
                $export
            ));
        }

        return $jobs;
    }

    /**
     * @return Collection<int, object>
     */
    private function exportView(
        FromView $sheetExport,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex,
        object $export
    ): Collection {
        $jobs = new Collection;
        $jobs->push(new AppendViewToSheet(
            $sheetExport,
            $temporaryFile,
            $writerType,
            $sheetIndex,
            $export
        ));

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
