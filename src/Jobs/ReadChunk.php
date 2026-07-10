<?php

namespace Maatwebsite\Excel\Jobs;

use DateTime;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Exceptions\SheetNotFoundException;
use Maatwebsite\Excel\Files\RemoteTemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Filters\ChunkReadFilter;
use Maatwebsite\Excel\HasEventBus;
use Maatwebsite\Excel\Helpers\QueueResolver;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Transactions\TransactionHandler;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Throwable;

class ReadChunk implements ShouldQueue
{
    use Batchable, HasEventBus, InteractsWithQueue, Queueable;

    public ?int $timeout;

    public ?int $tries;

    public ?int $maxExceptions;

    public ?int $backoff;

    /**
     * @var ?string
     */
    public $queue;

    /**
     * @var ?string
     */
    public $connection;

    private string $uniqueId;

    public function __construct(
        private WithChunkReading $import,
        private IReader $reader,
        private TemporaryFile $temporaryFile,
        private string $sheetName,
        private object $sheetImport,
        private int $startRow,
        private int $chunkSize,
    ) {
        $this->timeout       = $this->import->timeout ?? null;
        $this->tries         = $this->import->tries ?? null;
        $this->maxExceptions = $this->import->maxExceptions ?? null;
        $this->backoff       = method_exists($this->import, 'backoff') ? $this->import->backoff() : ($this->import->backoff ?? null);
        $this->connection    = QueueResolver::connection($this->import);
        $this->queue         = QueueResolver::queue($this->import);
    }

    public function getUniqueId(): string
    {
        if (!isset($this->uniqueId)) {
            $this->uniqueId = uniqid();
            Cache::set('laravel-excel/read-chunk/' . $this->uniqueId, true);
        }

        return $this->uniqueId;
    }

    public static function isComplete(string $id): bool
    {
        return !Cache::has('laravel-excel/read-chunk/' . $id);
    }

    /**
     * Get the middleware the job should be dispatched through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return (method_exists($this->import, 'middleware')) ? $this->import->middleware() : [];
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): ?DateTime
    {
        return (method_exists($this->import, 'retryUntil')) ? $this->import->retryUntil() : null;
    }

    /**
     * @throws SheetNotFoundException
     * @throws Exception
     */
    public function handle(TransactionHandler $transaction): void
    {
        // Determine if the batch has been cancelled...
        if ($this->batch()?->cancelled()) {
            return;
        }

        if (method_exists($this->import, 'setChunkOffset')) {
            $this->import->setChunkOffset($this->startRow);
        }

        if (method_exists($this->sheetImport, 'setChunkOffset')) {
            $this->sheetImport->setChunkOffset($this->startRow);
        }

        if ($this->sheetImport instanceof WithCustomValueBinder) {
            Cell::setValueBinder($this->sheetImport);
        }

        $headingRow = HeadingRowExtractor::headingRow($this->sheetImport);

        $filter = new ChunkReadFilter(
            $headingRow,
            $this->startRow,
            $this->chunkSize,
            $this->sheetName
        );

        $this->reader->setReadFilter($filter);
        $this->reader->setReadDataOnly(config('excel.imports.read_only', true));
        $this->reader->setReadEmptyCells(!config('excel.imports.ignore_empty', false));

        $spreadsheet = $this->reader->load(
            $this->temporaryFile->sync()->getLocalPath()
        );

        $sheet = Sheet::byName(
            $spreadsheet,
            $this->sheetName
        );

        if ($sheet->getHighestRow() < $this->startRow) {
            $sheet->disconnect();

            $this->cleanUpTempFile();

            return;
        }

        $transaction(function () use ($sheet): void {
            $sheet->import(
                $this->sheetImport,
                $this->startRow
            );

            $sheet->disconnect();

            $this->cleanUpTempFile();

            $sheet->raise(new AfterChunk($sheet, $this->import, $this->startRow));
        });
    }

    public function failed(Throwable $e): void
    {
        $this->cleanUpTempFile(true);

        if ($this->import instanceof WithEvents) {
            $this->registerListeners($this->import->registerEvents());
            $this->raise(new ImportFailed($e));

            if (method_exists($this->import, 'failed')) {
                $this->import->failed($e);
            }
        }
    }

    private function cleanUpTempFile(bool $force = false): bool
    {
        if (isset($this->uniqueId) && ($this->uniqueId !== '' && $this->uniqueId !== '0')) {
            Cache::delete('laravel-excel/read-chunk/' . $this->uniqueId);
        }

        if (!$force && !config('excel.temporary_files.force_resync_remote')) {
            return true;
        }

        if (!$this->temporaryFile instanceof RemoteTemporaryFile) {
            return true;
        }

        return $this->temporaryFile->deleteLocalCopy();
    }
}
