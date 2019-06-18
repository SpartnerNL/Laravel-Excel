<?php

namespace Seoperin\LaravelExcel;

use Traversable;
use Illuminate\Support\Collection;
use Seoperin\LaravelExcel\Jobs\CloseSheet;
use Seoperin\LaravelExcel\Jobs\QueueExport;
use Seoperin\LaravelExcel\Concerns\FromQuery;
use Seoperin\LaravelExcel\Files\TemporaryFile;
use Seoperin\LaravelExcel\Jobs\SerializedQuery;
use Seoperin\LaravelExcel\Jobs\AppendDataToSheet;
use Seoperin\LaravelExcel\Jobs\StoreQueuedExport;
use Seoperin\LaravelExcel\Concerns\FromCollection;
use Seoperin\LaravelExcel\Jobs\AppendQueryToSheet;
use Seoperin\LaravelExcel\Files\TemporaryFileFactory;
use Seoperin\LaravelExcel\Concerns\WithMultipleSheets;
use Seoperin\LaravelExcel\Concerns\WithCustomChunkSize;
use Seoperin\LaravelExcel\Concerns\WithCustomQuerySize;

class QueuedWriter
{
    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @var TemporaryFileFactory
     */
    protected $temporaryFileFactory;

    /**
     * @param Writer               $writer
     * @param TemporaryFileFactory $temporaryFileFactory
     */
    public function __construct(Writer $writer, TemporaryFileFactory $temporaryFileFactory)
    {
        $this->writer               = $writer;
        $this->chunkSize            = config('excel.exports.chunk_size', 1000);
        $this->temporaryFileFactory = $temporaryFileFactory;
    }

    /**
     * @param object       $export
     * @param string       $filePath
     * @param string       $disk
     * @param string|null  $writerType
     * @param array|string $diskOptions
     *
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null, $diskOptions = [])
    {
        $temporaryFile = $this->temporaryFileFactory->make();

        $jobs = $this->buildExportJobs($export, $temporaryFile, $writerType);

        $jobs->push(new StoreQueuedExport(
            $temporaryFile,
            $filePath,
            $disk,
            $diskOptions
        ));

        return QueueExport::withChain($jobs->toArray())->dispatch($export, $temporaryFile, $writerType);
    }

    /**
     * @param object        $export
     * @param TemporaryFile $temporaryFile
     * @param string        $writerType
     *
     * @return Collection
     */
    private function buildExportJobs($export, TemporaryFile $temporaryFile, string $writerType): Collection
    {
        $sheetExports = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheetExports = $export->sheets();
        }

        $jobs = new Collection;
        foreach ($sheetExports as $sheetIndex => $sheetExport) {
            if ($sheetExport instanceof FromCollection) {
                $jobs = $jobs->merge($this->exportCollection($sheetExport, $temporaryFile, $writerType, $sheetIndex));
            } elseif ($sheetExport instanceof FromQuery) {
                $jobs = $jobs->merge($this->exportQuery($sheetExport, $temporaryFile, $writerType, $sheetIndex));
            }

            $jobs->push(new CloseSheet($sheetExport, $temporaryFile, $writerType, $sheetIndex));
        }

        return $jobs;
    }

    /**
     * @param FromCollection $export
     * @param TemporaryFile  $temporaryFile
     * @param string         $writerType
     * @param int            $sheetIndex
     *
     * @return Collection
     */
    private function exportCollection(
        FromCollection $export,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex
    ): Collection {
        return $export
            ->collection()
            ->chunk($this->getChunkSize($export))
            ->map(function ($rows) use ($writerType, $temporaryFile, $sheetIndex, $export) {
                if ($rows instanceof Traversable) {
                    $rows = iterator_to_array($rows);
                }

                return new AppendDataToSheet(
                    $export,
                    $temporaryFile,
                    $writerType,
                    $sheetIndex,
                    $rows
                );
            });
    }

    /**
     * @param FromQuery     $export
     * @param TemporaryFile $temporaryFile
     * @param string        $writerType
     * @param int           $sheetIndex
     *
     * @return Collection
     */
    private function exportQuery(
        FromQuery $export,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex
    ): Collection {
        $query = $export->query();

        $count = $export instanceof WithCustomQuerySize ? $export->querySize() : $query->count();
        $spins = ceil($count / $this->getChunkSize($export));

        $jobs = new Collection();

        for ($page = 1; $page <= $spins; $page++) {
            $serializedQuery = new SerializedQuery(
                $query->forPage($page, $this->getChunkSize($export))
            );

            $jobs->push(new AppendQueryToSheet(
                $export,
                $temporaryFile,
                $writerType,
                $sheetIndex,
                $serializedQuery
            ));
        }

        return $jobs;
    }

    /**
     * @param object|WithCustomChunkSize $export
     *
     * @return int
     */
    private function getChunkSize($export): int
    {
        if ($export instanceof WithCustomChunkSize) {
            return $export->chunkSize();
        }

        return $this->chunkSize;
    }
}
