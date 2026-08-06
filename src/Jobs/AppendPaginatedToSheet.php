<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromScout;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\Middleware\LocalizeJob;
use Maatwebsite\Excel\Writer;
use PhpOffice\PhpSpreadsheet\Exception;

class AppendPaginatedToSheet implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, ProxyFailures, Queueable;

    public function __construct(
        public FromQuery|FromScout $sheetExport,
        public TemporaryFile $temporaryFile,
        public string $writerType,
        public int $sheetIndex,
        public int $page,
        public int $perPage,
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
            $writer = $writer->reopen($this->temporaryFile, $this->writerType);

            $sheet = $writer->getSheetByIndex($this->sheetIndex);

            $sheet->appendRows($this->chunk(), $this->sheetExport);

            $writer->write($this->export ?? $this->sheetExport, $this->temporaryFile, $this->writerType);
        });
    }

    /**
     * @return iterable<array-key, mixed>
     */
    protected function chunk(): iterable
    {
        if ($this->sheetExport instanceof FromScout) {
            return $this->sheetExport->scout()->paginate($this->perPage, 'page', $this->page)->items();
        }

        // Fallback
        return $this->sheetExport->query()->forPage($this->page, $this->perPage)->get();
    }
}
