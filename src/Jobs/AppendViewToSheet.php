<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\Middleware\LocalizeJob;
use Maatwebsite\Excel\Writer;
use PhpOffice\PhpSpreadsheet\Exception;

class AppendViewToSheet implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public FromView $sheetExport,
        public TemporaryFile $temporaryFile,
        public string $writerType,
        public int $sheetIndex,
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

            $sheet->fromView($this->sheetExport, $this->sheetIndex);

            $writer->write($this->sheetExport, $this->temporaryFile, $this->writerType);
        });
    }
}
