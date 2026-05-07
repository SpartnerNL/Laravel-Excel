<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Exceptions\NoSheetsFoundException;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\Middleware\LocalizeJob;
use Maatwebsite\Excel\Writer;
use Throwable;

class QueueExport implements ShouldQueue
{
    use Batchable, Dispatchable, ExtendedQueueable, InteractsWithQueue;

    /**
     * @param  object  $export
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     */
    public function __construct(public $export, private TemporaryFile $temporaryFile, private string $writerType)
    {
    }

    /**
     * Get the middleware the job should be dispatched through.
     *
     * @return array
     */
    public function middleware()
    {
        return (method_exists($this->export, 'middleware')) ? $this->export->middleware() : [];
    }

    /**
     * @param  Writer  $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function handle(Writer $writer)
    {
        // Determine if the batch has been cancelled...
        if ($this->batch()?->cancelled()) {
            return;
        }

        (new LocalizeJob($this->export))->handle($this, function () use ($writer) {
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
                $sheet = $writer->addNewSheet($sheetIndex);
                $sheet->open($sheetExport);
            }

            // Write to temp file with empty sheets.
            $writer->write($this->export, $this->temporaryFile, $this->writerType);
        });
    }

    /**
     * @param  Throwable  $e
     */
    public function failed(Throwable $e)
    {
        if (method_exists($this->export, 'failed')) {
            $this->export->failed($e);
        }
    }
}
