<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\Middleware\LocalizeJob;
use Maatwebsite\Excel\Writer;
use Throwable;

class QueueExport implements ShouldQueue
{
    use ExtendedQueueable, Dispatchable;

    /**
     * @var object
     */
    public $export;

    /**
     * @var string
     */
    private $writerType;

    /**
     * @var TemporaryFile
     */
    private $temporaryFile;

    /**
     * @param  object  $export
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     */
    public function __construct($export, TemporaryFile $temporaryFile, string $writerType)
    {
        $this->export        = $export;
        $this->writerType    = $writerType;
        $this->temporaryFile = $temporaryFile;
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
        (new LocalizeJob($this->export))->handle($this, function () use ($writer) {
            $writer->open($this->export);

            $sheetExports = [$this->export];
            if ($this->export instanceof WithMultipleSheets) {
                $sheetExports = $this->export->sheets();
            }

            // Pre-create the worksheets
            foreach ($sheetExports as $sheetIndex => $sheetExport) {
                $sheet = $writer->addNewSheet($sheetIndex);
                $sheet->open($sheetExport);
            }

            // Write to temp file with empty sheets.
            $writer->write($sheetExport, $this->temporaryFile, $this->writerType);
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
