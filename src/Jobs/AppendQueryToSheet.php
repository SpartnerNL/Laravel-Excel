<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\HasEventBus;
use Maatwebsite\Excel\Jobs\Middleware\LocalizeJob;
use Maatwebsite\Excel\Writer;
use PhpOffice\PhpSpreadsheet\Exception;

class AppendQueryToSheet implements ShouldQueue
{
    use Batchable, Dispatchable, HasEventBus, InteractsWithQueue, ProxyFailures, Queueable;

    public function __construct(
        public FromQuery $sheetExport,
        public TemporaryFile $temporaryFile,
        public string $writerType,
        public int $sheetIndex,
        public int $page,
        public int $chunkSize,
        public ?Export $export = null,
    ) {
    }

    /**
     * Get the middleware the job should be dispatched through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return (method_exists($this->sheetExport, 'middleware')) ? $this->sheetExport->middleware() : [];
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(Writer $writer): void
    {
        // Determine if the batch has been cancelled...
        if ($this->batch()?->cancelled()) {
            return;
        }

        (new LocalizeJob($this->sheetExport))->handle($this, function () use ($writer): void {
            if ($this->sheetExport instanceof WithEvents) {
                $this->registerListeners($this->sheetExport->registerEvents());
            }

            $writer = $writer->reopen($this->temporaryFile, $this->writerType);

            $sheet = $writer->getSheetByIndex($this->sheetIndex);

            $query = $this->sheetExport->query()->forPage($this->page, $this->chunkSize);

            $sheet->appendRows($query->get(), $this->sheetExport);

            $this->raise(new AfterChunk($sheet, $this->sheetExport, ($this->page - 1) * $this->chunkSize));

            $writer->write($this->export ?? $this->sheetExport, $this->temporaryFile, $this->writerType);

            $this->clearListeners();
        });
    }
}
