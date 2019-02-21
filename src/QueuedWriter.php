<?php

namespace Maatwebsite\Excel;

use Traversable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Jobs\CloseSheet;
use Maatwebsite\Excel\Jobs\QueueExport;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Jobs\SerializedQuery;
use Maatwebsite\Excel\Helpers\FilePathHelper;
use Maatwebsite\Excel\Jobs\AppendDataToSheet;
use Maatwebsite\Excel\Jobs\StoreQueuedExport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Jobs\AppendQueryToSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithCustomQuerySize;

class QueuedWriter
{
    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var FilePathHelper
     */
    protected $filePathHelper;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @param Writer         $writer
     * @param FilePathHelper $filePathHelper
     */
    public function __construct(Writer $writer, FilePathHelper $filePathHelper)
    {
        $this->writer         = $writer;
        $this->filePathHelper = $filePathHelper;
        $this->chunkSize      = config('excel.exports.chunk_size', 1000);
    }

    /**
     * @param object      $export
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     * @param mixed       $diskOptions
     *
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null, $diskOptions = [])
    {
        $tempFile = $this->filePathHelper->generateTempFileName(true);

        $jobs = $this->buildExportJobs($export, $tempFile, $writerType);

        $jobs->push(new StoreQueuedExport($tempFile, $filePath, $disk, $diskOptions));

        return QueueExport::withChain($jobs->toArray())->dispatch($export, $tempFile, $writerType);
    }

    /**
     * @param object $export
     * @param string $tempFile
     * @param string $writerType
     *
     * @return Collection
     */
    private function buildExportJobs($export, string $tempFile, string $writerType)
    {
        $sheetExports = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheetExports = $export->sheets();
        }

        $jobs = new Collection;
        foreach ($sheetExports as $sheetIndex => $sheetExport) {
            if ($sheetExport instanceof FromCollection) {
                $jobs = $jobs->merge($this->exportCollection($sheetExport, $tempFile, $writerType, $sheetIndex));
            } elseif ($sheetExport instanceof FromQuery) {
                $jobs = $jobs->merge($this->exportQuery($sheetExport, $tempFile, $writerType, $sheetIndex));
            }

            $jobs->push(new CloseSheet($sheetExport, $tempFile, $writerType, $sheetIndex));
        }

        return $jobs;
    }

    /**
     * @param FromCollection $export
     * @param string         $fileName
     * @param string         $writerType
     * @param int            $sheetIndex
     *
     * @return Collection
     */
    private function exportCollection(
        FromCollection $export,
        string $fileName,
        string $writerType,
        int $sheetIndex
    ) {
        return $export
            ->collection()
            ->chunk($this->getChunkSize($export))
            ->map(function ($rows) use ($writerType, $fileName, $sheetIndex, $export) {
                if ($rows instanceof Traversable) {
                    $rows = iterator_to_array($rows);
                }

                return new AppendDataToSheet(
                    $export,
                    $fileName,
                    $writerType,
                    $sheetIndex,
                    $rows
                );
            });
    }

    /**
     * @param FromQuery $export
     * @param string    $fileName
     * @param string    $writerType
     * @param int       $sheetIndex
     *
     * @return Collection
     */
    private function exportQuery(
        FromQuery $export,
        string $fileName,
        string $writerType,
        int $sheetIndex
    ) {
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
                $fileName,
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
