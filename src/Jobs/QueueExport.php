<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Exceptions\NoSheetsFoundException;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\Middleware\LocalizeJob;
use Maatwebsite\Excel\Writer;
use PhpOffice\PhpSpreadsheet\Exception;
use Throwable;

class QueueExport implements ShouldQueue
{
    use Batchable, Dispatchable, ExtendedQueueable, InteractsWithQueue;

    public function __construct(
        public Export $export,
        private readonly TemporaryFile $temporaryFile,
        private readonly string $writerType,
    ) {
    }

    /**
     * Get the middleware the job should be dispatched through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return (method_exists($this->export, 'middleware')) ? $this->export->middleware() : [];
    }

    /**
     * @throws Exception
     */
    public function handle(Writer $writer): void
    {
        // Determine if the batch has been cancelled...
        if ($this->batch()?->cancelled()) {
            return;
        }

        (new LocalizeJob($this->export))->handle($this, function () use ($writer): void {
            $writer->open($this->export);

            $sheetExports = [$this->export];
            if ($this->export instanceof WithMultipleSheets) {
                $sheetExports = $this->export->sheets();
            }

            if (count($sheetExports) === 0) {
                throw new NoSheetsFoundException('Your export did not return any sheet export instances, please make sure your sheets() method always at least returns one instance.');
            }

            // Pre-create the worksheets
            foreach ($sheetExports as $sheetIndex => $sheetExport) {
                $sheet = $writer->getSheetForExport($sheetIndex);
                $sheet->open($sheetExport);
            }

            // Write to temp file with empty sheets.
            $writer->write($this->export, $this->temporaryFile, $this->writerType);
        });
    }

    public function failed(Throwable $e): void
    {
        if (method_exists($this->export, 'failed')) {
            $this->export->failed($e);
        }
    }
}
